<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $lead['type_label'] ?? 'Yeni Talep' }}</title>
</head>
<body style="margin:0;padding:0;background-color:#f1f5f9;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Arial,sans-serif;">

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f1f5f9;padding:24px 12px;">
    <tr>
        <td align="center">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;background-color:#ffffff;border-radius:12px;overflow:hidden;border:1px solid #e2e8f0;">

                {{-- Başlık --}}
                <tr>
                    <td style="background-color:#4f46e5;padding:24px 28px;">
                        <p style="margin:0 0 4px;color:#c7d2fe;font-size:12px;font-weight:600;letter-spacing:.08em;text-transform:uppercase;">
                            WOXO Software
                        </p>
                        <h1 style="margin:0;color:#ffffff;font-size:20px;font-weight:700;">
                            {{ $lead['type_label'] ?? 'Yeni Talep' }}
                        </h1>
                    </td>
                </tr>

                {{-- Özet --}}
                <tr>
                    <td style="padding:24px 28px 8px;">
                        <p style="margin:0 0 20px;color:#475569;font-size:14px;line-height:1.6;">
                            Web sitenizden yeni bir talep geldi. Detaylar aşağıdadır.
                        </p>

                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
                            @foreach ($lead['fields'] as $label => $value)
                                @if (!empty($value))
                                    <tr>
                                        <td style="padding:10px 0;border-bottom:1px solid #f1f5f9;color:#64748b;font-size:13px;width:38%;vertical-align:top;">
                                            {{ $label }}
                                        </td>
                                        <td style="padding:10px 0;border-bottom:1px solid #f1f5f9;color:#0f172a;font-size:14px;font-weight:600;vertical-align:top;">
                                            @if ($label === 'E-posta')
                                                <a href="mailto:{{ $value }}" style="color:#4f46e5;text-decoration:none;">{{ $value }}</a>
                                            @elseif ($label === 'Telefon')
                                                <a href="tel:{{ preg_replace('/[^0-9+]/', '', $value) }}" style="color:#4f46e5;text-decoration:none;">{{ $value }}</a>
                                            @else
                                                {{ $value }}
                                            @endif
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </table>
                    </td>
                </tr>

                {{-- Mesaj --}}
                @if (!empty($lead['message']))
                    <tr>
                        <td style="padding:12px 28px 4px;">
                            <p style="margin:0 0 8px;color:#64748b;font-size:12px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;">
                                Mesaj
                            </p>
                            <div style="background-color:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:14px 16px;color:#334155;font-size:14px;line-height:1.7;white-space:pre-wrap;">{{ $lead['message'] }}</div>
                        </td>
                    </tr>
                @endif

                {{-- Panel bağlantısı --}}
                @if (!empty($lead['admin_link']))
                    <tr>
                        <td style="padding:24px 28px 8px;">
                            <a href="{{ $lead['admin_link'] }}"
                               style="display:inline-block;background-color:#4f46e5;color:#ffffff;text-decoration:none;font-size:14px;font-weight:600;padding:12px 24px;border-radius:8px;">
                                Panelde Görüntüle
                            </a>
                        </td>
                    </tr>
                @endif

                {{-- Teknik bilgiler --}}
                @if (!empty($lead['meta']))
                    <tr>
                        <td style="padding:16px 28px 8px;">
                            <p style="margin:0 0 8px;color:#94a3b8;font-size:11px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;">
                                Teknik Bilgiler
                            </p>
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                @foreach ($lead['meta'] as $label => $value)
                                    @if (!empty($value))
                                        <tr>
                                            <td style="padding:3px 0;color:#94a3b8;font-size:12px;width:38%;">{{ $label }}</td>
                                            <td style="padding:3px 0;color:#64748b;font-size:12px;word-break:break-all;">{{ $value }}</td>
                                        </tr>
                                    @endif
                                @endforeach
                            </table>
                        </td>
                    </tr>
                @endif

                {{-- Alt bilgi --}}
                <tr>
                    <td style="padding:20px 28px 26px;border-top:1px solid #f1f5f9;">
                        <p style="margin:0;color:#94a3b8;font-size:12px;line-height:1.6;">
                            Bu e-posta woxoyazilim.com üzerinden otomatik gönderilmiştir.
                            Talebi yanıtlamak için doğrudan bu e-postayı yanıtlayabilirsiniz.
                        </p>
                    </td>
                </tr>

            </table>
        </td>
    </tr>
</table>

</body>
</html>
