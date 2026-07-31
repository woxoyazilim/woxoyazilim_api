<?php

namespace App\Services;

use App\Mail\NewLeadMail;
use App\Models\ChatLead;
use App\Models\DemoRequest;
use App\Models\Message;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Siteden gelen taleplerin bildirim e-postasını gönderir.
 *
 * ÖNEMLİ: Gönderim hataları asla dışarı sızdırılmaz. SMTP çalışmasa bile
 * talep veritabanına kaydedilmiş olur ve ziyaretçi hata görmez; sorun
 * yalnızca log'a yazılır.
 */
class LeadMailService
{
    /** İletişim formu / "Teklif Al" modalı → messages tablosu */
    public function sendContactMessage(Message $msg): void
    {
        $this->dispatch([
            'type' => 'contact',
            'type_label' => 'Yeni İletişim / Teklif Talebi',
            'name' => $msg->name,
            'email' => $msg->email,
            'message' => $msg->message,
            'fields' => [
                'Ad Soyad' => $msg->name,
                'E-posta' => $msg->email,
                'Telefon' => $msg->phone,
                'Firma' => $msg->company,
                'İlgilendiği Hizmet' => $msg->subject,
            ],
            'meta' => [
                'Konum' => trim(implode(', ', array_filter([$msg->city, $msg->country]))),
                'Cihaz' => trim(implode(' / ', array_filter([$msg->device_type, $msg->browser, $msg->os]))),
                'IP' => $msg->ip_address,
                'Geldiği Sayfa' => $msg->source_url,
                'Tarih' => optional($msg->created_at)->format('d.m.Y H:i'),
            ],
            'admin_link' => config('leads.admin_url') . '/admin/messages/' . $msg->id,
        ]);
    }

    /** Demo talebi / hizmet sayfası ve referans teklif formları → demo_requests tablosu */
    public function sendDemoRequest(DemoRequest $dr): void
    {
        $isReferenceQuote = str_starts_with((string) $dr->software_slug, 'referans/');

        $this->dispatch([
            'type' => 'demo',
            'type_label' => $isReferenceQuote
                ? 'Yeni Referans Teklif Talebi'
                : 'Yeni Demo / Teklif Talebi',
            'name' => $dr->name,
            'email' => $dr->email,
            'message' => $dr->message,
            'fields' => [
                'Ad Soyad' => $dr->name,
                'E-posta' => $dr->email,
                'Telefon' => $dr->phone,
                'Firma' => $dr->company,
                'İlgilendiği Hizmet' => $dr->software_name,
                'Çalışan Sayısı' => $dr->employee_count,
            ],
            'meta' => [
                'Kaynak' => $dr->software_slug,
                'Tarih' => optional($dr->created_at)->format('d.m.Y H:i'),
            ],
            'admin_link' => config('leads.admin_url') . '/admin/demo-requests',
        ]);
    }

    /** ChatBot üzerinden bırakılan iletişim bilgisi → chat_leads tablosu */
    public function sendChatLead(ChatLead $lead): void
    {
        $this->dispatch([
            'type' => 'chat',
            'type_label' => 'Yeni ChatBot Kaydı',
            'name' => $lead->name,
            'email' => $lead->email,
            'message' => is_string($lead->messages) ? $lead->messages : null,
            'fields' => [
                'Ad Soyad' => $lead->name,
                'E-posta' => $lead->email,
                'Telefon' => $lead->phone,
            ],
            'meta' => [
                'Kaynak' => $lead->source,
                'Tarih' => optional($lead->created_at)->format('d.m.Y H:i'),
            ],
            'admin_link' => config('leads.admin_url') . '/admin/chat-leads',
        ]);
    }

    /**
     * Gerçek gönderim. Hiçbir koşulda exception fırlatmaz.
     */
    protected function dispatch(array $lead): void
    {
        try {
            if (!config('leads.notify_enabled')) {
                return;
            }

            $recipients = config('leads.notify_emails', []);
            if (empty($recipients)) {
                Log::warning('LeadMailService: bildirim adresi tanımlı değil, e-posta gönderilmedi.');
                return;
            }

            Mail::to($recipients)->send(new NewLeadMail($lead));

            Log::info('LeadMailService: bildirim gönderildi.', [
                'type' => $lead['type'] ?? null,
                'to' => $recipients,
            ]);
        } catch (\Throwable $e) {
            // Talep zaten kaydedildi; e-posta gönderilemese bile akış bozulmamalı.
            Log::error('LeadMailService: bildirim e-postası gönderilemedi.', [
                'type' => $lead['type'] ?? null,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
