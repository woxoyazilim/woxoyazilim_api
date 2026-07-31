<?php

namespace App\Console\Commands;

use App\Mail\NewLeadMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

/**
 * Talep bildirim e-postasının çalışıp çalışmadığını test eder.
 *
 *   php artisan lead:test-mail
 *   php artisan lead:test-mail baska@adres.com
 */
class TestLeadMail extends Command
{
    protected $signature = 'lead:test-mail {email? : Test e-postasının gönderileceği adres}';

    protected $description = 'Talep bildirim e-postasını test amaçlı gönderir';

    public function handle(): int
    {
        $recipients = $this->argument('email')
            ? [$this->argument('email')]
            : config('leads.notify_emails', []);

        if (empty($recipients)) {
            $this->error('Alıcı adresi bulunamadı. .env içinde LEAD_NOTIFY_EMAIL tanımlayın.');
            return self::FAILURE;
        }

        $this->line('Mailer      : ' . config('mail.default'));
        $this->line('SMTP host   : ' . config('mail.mailers.smtp.host'));
        $this->line('Gönderen    : ' . config('mail.from.address'));
        $this->line('Alıcı(lar)  : ' . implode(', ', $recipients));
        $this->newLine();

        $lead = [
            'type' => 'test',
            'type_label' => 'TEST — Bildirim E-postası',
            'name' => 'Test Kaydı',
            'email' => 'test@woxoyazilim.com',
            'message' => "Bu bir test e-postasıdır.\nBu mesajı aldıysanız talep bildirimleri çalışıyor demektir.",
            'fields' => [
                'Ad Soyad' => 'Test Kaydı',
                'E-posta' => 'test@woxoyazilim.com',
                'Telefon' => '0531 254 71 51',
                'Firma' => 'WOXO Software',
                'İlgilendiği Hizmet' => 'E-Ticaret Yazılımı',
            ],
            'meta' => [
                'Kaynak' => 'lead:test-mail komutu',
                'Tarih' => now()->format('d.m.Y H:i'),
            ],
            'admin_link' => config('leads.admin_url') . '/admin/demo-requests',
        ];

        try {
            Mail::to($recipients)->send(new NewLeadMail($lead));
            $this->info('Test e-postası gönderildi. Gelen kutusunu (ve spam klasörünü) kontrol edin.');
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Gönderilemedi: ' . $e->getMessage());
            $this->newLine();
            $this->warn('.env içindeki MAIL_* ayarlarını kontrol edin, ardından: php artisan config:clear');
            return self::FAILURE;
        }
    }
}
