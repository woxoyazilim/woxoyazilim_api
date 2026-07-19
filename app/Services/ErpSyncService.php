<?php

namespace App\Services;

use App\Models\Message;
use App\Models\MessageActivity;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ErpSyncService
{
    /**
     * Sync a message/lead to the ERP system as a customer
     */
    public function syncMessageToCustomer(Message $msg, User $actor): array
    {
        // Idempotency: already synced
        if ($msg->erp_customer_id && $msg->erp_sync_status === 'synced') {
            return [
                'success' => false,
                'error' => 'Bu talep zaten ERP sistemine aktarılmış.',
                'erp_customer_id' => $msg->erp_customer_id,
            ];
        }

        $erpUrl = config('services.erp.url');
        if (!$erpUrl) {
            $msg->update(['erp_sync_status' => 'failed']);
            $this->logActivity($msg, $actor, 'erp_sync_failed', null, null, 'ERP API URL yapılandırılmamış. .env dosyasında ERP_API_URL ayarlayın.');

            return [
                'success' => false,
                'error' => 'ERP API URL yapılandırılmamış. Sistem yöneticisiyle iletişime geçin.',
            ];
        }

        try {
            $response = Http::timeout(15)
                ->post("{$erpUrl}/customers", [
                    'type' => 'individual',
                    'name' => $msg->name,
                    'companyName' => $msg->company,
                    'email' => $msg->email,
                    'phone' => $msg->phone,
                    'source' => 'website_contact_form',
                    'status' => 'lead',
                    'notes' => implode("\n", array_filter([
                        "Hizmet: " . ($msg->subject ?: '-'),
                        "Mesaj: " . $msg->message,
                        "Kaynak: " . ($msg->source ?: 'contact_form'),
                        $msg->country ? "Konum: {$msg->city}, {$msg->country}" : null,
                        "WOXO Talep ID: " . $msg->id,
                    ])),
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $erpId = $data['customer']['id'] ?? $data['id'] ?? null;

                $msg->update([
                    'erp_customer_id' => $erpId,
                    'erp_sync_status' => 'synced',
                    'erp_synced_at' => now(),
                    'erp_synced_by' => $actor->id,
                ]);

                $this->logActivity($msg, $actor, 'erp_synced', null, $erpId, 'ERP sistemine başarıyla aktarıldı.');

                return ['success' => true, 'erp_customer_id' => $erpId];
            }

            $errorMsg = $response->json('error') ?? $response->json('message') ?? 'Bilinmeyen hata';
            $msg->update(['erp_sync_status' => 'failed']);
            $this->logActivity($msg, $actor, 'erp_sync_failed', null, null, "ERP API hatası: {$errorMsg}");

            Log::error('ERP sync failed', ['message_id' => $msg->id, 'status' => $response->status(), 'body' => $response->body()]);

            return ['success' => false, 'error' => "ERP aktarımı başarısız: {$errorMsg}"];

        } catch (\Exception $e) {
            $msg->update(['erp_sync_status' => 'failed']);
            $this->logActivity($msg, $actor, 'erp_sync_failed', null, null, "Bağlantı hatası: {$e->getMessage()}");

            Log::error('ERP sync exception', ['message_id' => $msg->id, 'error' => $e->getMessage()]);

            return ['success' => false, 'error' => 'ERP sunucusuna bağlanılamadı. Lütfen tekrar deneyin.'];
        }
    }

    private function logActivity(Message $msg, User $actor, string $action, ?string $oldValue, ?string $newValue, ?string $details): void
    {
        MessageActivity::create([
            'message_id' => $msg->id,
            'user_id' => $actor->id,
            'user_name' => $actor->name,
            'action' => $action,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'details' => $details,
        ]);
    }
}
