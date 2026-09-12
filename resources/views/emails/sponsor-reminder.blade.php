<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $payload->subject }}</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f4f5f7; color: #1e293b; margin: 0; padding: 24px; line-height: 1.6; }
        .card { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
        .header { background: #0f766e; color: #ffffff; padding: 24px 32px; text-align: center; }
        .header h1 { margin: 0; font-size: 20px; font-weight: 600; }
        .content { padding: 32px; }
        .badge { display: inline-block; background: #f0fdf4; color: #15803d; padding: 4px 10px; border-radius: 4px; font-size: 13px; font-weight: 600; margin-bottom: 16px; }
        .highlight-box { background: #f8fafc; border-left: 4px solid #0f766e; padding: 16px; margin: 20px 0; border-radius: 4px; }
        .amount { font-size: 24px; font-weight: 700; color: #0f766e; margin: 4px 0; }
        .due-date { font-size: 14px; color: #64748b; }
        .instructions { background: #f1f5f9; padding: 16px; border-radius: 6px; font-size: 14px; margin: 20px 0; }
        .footer { padding: 20px 32px; text-align: center; font-size: 12px; color: #94a3b8; border-top: 1px solid #e2e8f0; }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <h1>Yayasan Peduli Anak Yatim</h1>
        </div>
        <div class="content">
            <span class="badge">{{ $payload->waveLabel }}</span>
            <p>Assalamu'alaikum Wr. Wb. / Salam Sejahtera,</p>
            <p>Yth. Bpk/Ibu <strong>{{ $payload->sponsorName }}</strong>,</p>

            <p>Semoga Bpk/Ibu senantiasa dalam keadaan sehat dan penuh berkah. Kami dari pengurus yayasan ingin menginformasikan jadwal komitmen donasi sponsor rutin @if($payload->orphanName) untuk anak asuh tercinta <strong>{{ $payload->orphanName }}</strong> @endif sebagai berikut:</p>

            <div class="highlight-box">
                <div class="due-date">Tanggal Jatuh Tempo: <strong>{{ \Carbon\Carbon::parse($payload->dueDate)->translatedFormat('d F Y') }}</strong></div>
                <div class="amount">{{ $payload->formattedAmount }}</div>
            </div>

            <p>Donasi dapat disalurkan melalui rekening resmi yayasan:</p>
            <div class="instructions">
                <strong>Rekening Donasi:</strong><br>
                • <strong>Bank Syariah Indonesia (BSI)</strong>: 123-456-7890<br>
                • <strong>Bank Mandiri</strong>: 987-654-3210<br>
                <em>a.n. Yayasan Peduli Anak</em>
            </div>

            <p>Setelah melakukan transfer, mohon konfirmasikan bukti transfer kepada kami agar dapat dicatatkan dalam laporan donasi bulanan.</p>

            <p>Jazakumullah khairan katsiran atas ketulusan dan kepedulian Bpk/Ibu dalam mendukung masa depan anak-anak asuh kami.</p>

            <p style="margin-top: 28px;">
                Salam hangat,<br>
                <strong>Pengurus Yayasan Peduli Anak Yatim</strong>
            </p>
        </div>
        <div class="footer">
            Email ini dikirim secara otomatis oleh Rymainder Notification System. Mohon jangan membalas langsung ke alamat no-reply ini.
        </div>
    </div>
</body>
</html>
