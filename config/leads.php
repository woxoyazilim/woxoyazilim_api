<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Talep Bildirim E-postaları
    |--------------------------------------------------------------------------
    |
    | Siteden gelen her yeni talep (iletişim formu, demo talebi, referans
    | teklif formu, chatbot kaydı) aşağıdaki adreslere bildirim olarak
    | gönderilir. Birden fazla adres için .env içinde virgülle ayırın:
    |
    |   LEAD_NOTIFY_EMAIL="info@woxoyazilim.com,satis@woxoyazilim.com"
    |
    */

    'notify_emails' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('LEAD_NOTIFY_EMAIL', 'info@woxoyazilim.com'))
    ))),

    /*
    |--------------------------------------------------------------------------
    | Bildirim E-postalarını Aç / Kapat
    |--------------------------------------------------------------------------
    |
    | SMTP ayarları yapılmadan önce false bırakılabilir. Kapalıyken talepler
    | yine kaydedilir ve admin panelde görünür; yalnızca e-posta gönderilmez.
    |
    */

    'notify_enabled' => filter_var(env('LEAD_NOTIFY_ENABLED', true), FILTER_VALIDATE_BOOLEAN),

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Adresi
    |--------------------------------------------------------------------------
    |
    | E-postadaki "Panelde Görüntüle" bağlantısı bu adresle oluşturulur.
    |
    */

    'admin_url' => rtrim((string) env('ADMIN_PANEL_URL', 'https://www.woxoyazilim.com'), '/'),

];
