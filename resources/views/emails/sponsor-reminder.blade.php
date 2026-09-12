<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $payload->subject }}</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f4f5f7; color: #1e293b; margin: 0; padding: 24px; line-height: 1.6; }
        .card { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
        .header { background: #0f172a; color: #ffffff; padding: 24px 32px; text-align: center; }
        .header h1 { margin: 0; font-size: 20px; font-weight: 700; letter-spacing: -0.025em; }
        .content { padding: 32px; }
        .badge { display: inline-block; background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: 600; margin-bottom: 20px; }
        .message-body { font-size: 14px; line-height: 1.7; color: #334155; }
        .highlight-box { background: #f8fafc; border: 1px solid #e2e8f0; border-left: 4px solid #0f172a; padding: 16px; margin-bottom: 20px; border-radius: 6px; }
        .amount { font-size: 22px; font-weight: 700; color: #0f172a; margin: 4px 0; }
        .due-date { font-size: 13px; color: #64748b; }
        .footer { padding: 20px 32px; text-align: center; font-size: 12px; color: #94a3b8; border-top: 1px solid #e2e8f0; background: #f8fafc; }
    </style>
</head>
<body>
    @php
        $platformName = \App\Models\PlatformSetting::getName();
    @endphp
    <div class="card">
        <div class="header">
            <h1>{{ $platformName }}</h1>
        </div>
        <div class="content">
            <span class="badge">{{ $payload->waveLabel }}</span>

            <div class="highlight-box">
                <div class="due-date">Target Jatuh Tempo: <strong>{{ \Carbon\Carbon::parse($payload->dueDate)->translatedFormat('d F Y') }}</strong></div>
                <div class="amount">{{ $payload->formattedAmount }}</div>
            </div>

            <div class="message-body">{!! nl2br(e($payload->messageBody)) !!}</div>
        </div>
        <div class="footer">
            Email ini dikirim secara otomatis oleh sistem notifikasi {{ $platformName }}.
        </div>
    </div>
</body>
</html>
