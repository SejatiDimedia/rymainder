<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Ringkasan Harian Pengiriman Reminder Sponsor</title>
    <style>
        body { font-family: sans-serif; background-color: #f8fafc; color: #0f172a; padding: 20px; }
        .card { max-width: 650px; margin: 0 auto; background: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0; padding: 24px; }
        .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin: 20px 0; }
        .stat-box { background: #f1f5f9; padding: 12px; border-radius: 6px; text-align: center; }
        .stat-val { font-size: 22px; font-weight: bold; margin-top: 4px; }
        .success { color: #16a34a; }
        .danger { color: #dc2626; }
        .neutral { color: #64748b; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; font-size: 13px; }
        th, td { border: 1px solid #e2e8f0; padding: 8px 12px; text-align: left; }
        th { background: #f8fafc; }
    </style>
</head>
<body>
    <div class="card">
        <h2 style="margin-top:0; color:#0f766e;">Ringkasan Harian Reminder Donasi</h2>
        <p>Laporan otomatis aktivitas pengiriman reminder per tanggal <strong>{{ now()->translatedFormat('d F Y') }}</strong>:</p>

        <div class="stats-grid">
            <div class="stat-box">
                <div style="font-size:12px; color:#64748b;">Berhasil Terkirim</div>
                <div class="stat-val success">{{ $sentCount }}</div>
            </div>
            <div class="stat-box">
                <div style="font-size:12px; color:#64748b;">Gagal Terkirim</div>
                <div class="stat-val danger">{{ $failedCount }}</div>
            </div>
            <div class="stat-box">
                <div style="font-size:12px; color:#64748b;">Dilewati (Belum Onboard)</div>
                <div class="stat-val neutral">{{ $skippedCount }}</div>
            </div>
        </div>

        @if(count($failedLogs) > 0)
            <h3 style="color:#dc2626; margin-top:24px; font-size:15px;">Daftar Pengiriman Gagal (Perlu Follow-up Manual):</h3>
            <table>
                <thead>
                    <tr>
                        <th>Sponsor</th>
                        <th>Channel</th>
                        <th>Kontak</th>
                        <th>Pesan Error</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($failedLogs as $log)
                        <tr>
                            <td><strong>{{ $log->sponsor->name }}</strong></td>
                            <td>{{ $log->channel->label() }}</td>
                            <td>{{ $log->channel->value === 'email' ? $log->sponsor->email : $log->sponsor->phone }}</td>
                            <td style="color:#dc2626;">{{ $log->error_message }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p style="color:#16a34a; font-weight:600;">Alhamdulillah, tidak ada kendala pengiriman hari ini.</p>
        @endif

        <p style="margin-top:24px; font-size:12px; color:#94a3b8;">
            Laporan dibuat otomatis oleh sistem Rymainder pada {{ now()->toDateTimeString() }}.
        </p>
    </div>
</body>
</html>
