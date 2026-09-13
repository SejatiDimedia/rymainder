<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Undangan Menghubungkan Bot Pengingat Donasi - {{ $platformName }}</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f8fafc; color: #1e293b; margin: 0; padding: 24px; line-height: 1.6; }
        .card { max-width: 580px; margin: 0 auto; background: #ffffff; border-radius: 16px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04); }
        .header { background: #0f172a; color: #ffffff; padding: 28px 32px; text-align: center; }
        .header h1 { margin: 0; font-size: 20px; font-weight: 700; letter-spacing: -0.025em; }
        .header p { margin: 6px 0 0 0; font-size: 12px; color: #94a3b8; }
        .content { padding: 32px; }
        .badge { display: inline-flex; align-items: center; background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; padding: 5px 12px; border-radius: 9999px; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 20px; }
        .greeting { font-size: 16px; font-weight: 700; color: #0f172a; margin-bottom: 12px; }
        .paragraph { font-size: 14px; line-height: 1.7; color: #475569; margin-bottom: 16px; }
        
        .steps-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; margin: 24px 0; }
        .step-item { display: flex; align-items: flex-start; margin-bottom: 14px; font-size: 13px; color: #334155; }
        .step-item:last-child { margin-bottom: 0; }
        .step-number { background: #0f172a; color: #ffffff; width: 22px; height: 22px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 11px; font-weight: bold; margin-right: 12px; flex-shrink: 0; }
        
        .cta-container { text-align: center; margin: 32px 0; }
        .cta-btn { display: inline-block; background: #0088cc; color: #ffffff !important; text-decoration: none; padding: 14px 28px; border-radius: 10px; font-size: 14px; font-weight: 600; box-shadow: 0 4px 10px rgba(0, 136, 204, 0.25); }
        
        .code-box { background: #f1f5f9; border: 1px dashed #cbd5e1; border-radius: 8px; padding: 12px; text-align: center; font-size: 12px; color: #64748b; margin-top: 20px; }
        .code-box strong { color: #0f172a; font-family: monospace; font-size: 14px; letter-spacing: 1px; }
        
        .footer { padding: 20px 32px; text-align: center; font-size: 12px; color: #94a3b8; border-top: 1px solid #f1f5f9; background: #fafafa; }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <h1>{{ $platformName }}</h1>
            <p>Sistem Pengingat & Notifikasi Donasi Otomatis</p>
        </div>
        <div class="content">
            <span class="badge">Aktivasi Bot Telegram</span>
            
            <div class="greeting">Assalamu'alaikum Wr. Wb. / Salam Sejahtera, Bpk/Ibu {{ $sponsor->name }}</div>
            
            <p class="paragraph">
                Terima kasih atas kebaikan dan komitmen Bpk/Ibu dalam mendukung program donasi rutin di <strong>{{ $platformName }}</strong>.
            </p>
            
            <p class="paragraph">
                Untuk mempermudah Bpk/Ibu mendapatkan informasi jadwal pengingat dan laporan donasi secara cepat, tepat waktu, dan bebas repot, kami telah menyediakan layanan notifikasi langsung melalui <strong>Bot Resmi Telegram</strong>.
            </p>

            <div class="steps-box">
                <div class="step-item">
                    <span class="step-number">1</span>
                    <span>Klik tombol biru <strong>"Hubungkan ke Bot Telegram"</strong> di bawah ini.</span>
                </div>
                <div class="step-item">
                    <span class="step-number">2</span>
                    <span>Pada aplikasi Telegram yang terbuka, tekan tombol <strong>"START"</strong> di bagian bawah layar.</span>
                </div>
            </div>

            <div class="cta-container">
                <a href="{{ $onboardUrl }}" class="cta-btn" target="_blank">
                    ✈️ Hubungkan ke Bot Telegram
                </a>
            </div>

            <p class="paragraph" style="font-size: 12px; color: #64748b; text-align: center;">
                <em>Hanya perlu 1 klik. Sistem kami akan otomatis mengenali akun donatur Bpk/Ibu tanpa perlu mendaftar ulang.</em>
            </p>

            <div class="code-box">
                Jika tombol di atas tidak dapat diklik, salin tautan berikut ke browser Anda:<br>
                <a href="{{ $onboardUrl }}" style="color: #0088cc; word-break: break-all;">{{ $onboardUrl }}</a><br>
                <span style="font-size: 11px; display: inline-block; margin-top: 4px;">Kode Aktivasi Khusus: <strong>{{ $sponsor->telegram_onboard_code }}</strong></span>
            </div>
        </div>
        
        <div class="footer">
            Pesan ini dikirimkan secara otomatis oleh {{ $platformName }}.<br>
            Apabila Bpk/Ibu membutuhkan bantuan, silakan hubungi tim sekretariat kami.
        </div>
    </div>
</body>
</html>
