<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f9fafb;
            color: #374151;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            -webkit-text-size-adjust: none;
        }
        .wrapper {
            width: 100%;
            background-color: #f9fafb;
            padding: 20px 0;
        }
        .content {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            border: 1px solid #e5e7eb;
        }
        .header {
            padding: 24px;
            text-align: center;
            border-bottom: 1px solid #e5e7eb;
            background-color: #ffffff;
        }
        .header img {
            max-height: 50px;
        }
        .header p {
            margin: 8px 0 0 0;
            font-size: 14px;
            color: #6b7280;
            font-weight: 500;
        }
        .body {
            padding: 32px;
        }
        .footer {
            padding: 24px;
            text-align: center;
            font-size: 12px;
            color: #9ca3af;
            background-color: #f9fafb;
            border-top: 1px solid #e5e7eb;
        }
        .button {
            display: inline-block;
            background-color: #2563eb;
            color: #ffffff !important;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            margin-top: 16px;
        }
        .button:hover {
            background-color: #1d4ed8;
        }
        h1 {
            font-size: 20px;
            font-weight: 700;
            color: #111827;
            margin-top: 0;
        }
        p {
            margin-bottom: 16px;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="content">
            <div class="header">
                @if($settings['email_logo_url'] ?? false)
                    <img src="{{ $settings['email_logo_url'] }}" alt="{{ $settings['email_sender_name'] ?? 'TeamVora' }}">
                @else
                    <h2>{{ $settings['email_sender_name'] ?? 'TeamVora' }}</h2>
                @endif
            </div>
            
            <div class="body">
                @yield('content')
            </div>
            
            <div class="footer">
                <p>Dikirim oleh {{ $settings['email_sender_name'] ?? 'TeamVora' }}</p>
                @if($settings['email_reply_to'] ?? false)
                    <p>Balas ke: <a href="mailto:{{ $settings['email_reply_to'] }}" style="color: #6b7280;">{{ $settings['email_reply_to'] }}</a></p>
                @endif
                <p>&copy; {{ date('Y') }} TeamVora. Hak Cipta Dilindungi.</p>
            </div>
        </div>
    </div>
</body>
</html>
