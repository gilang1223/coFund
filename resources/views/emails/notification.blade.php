<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject }}</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #1A1A2E;
            font-family: 'Inter', Arial, sans-serif;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        .header {
            text-align: center;
            padding: 30px 0;
        }
        .logo {
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .logo-icon {
            width: 36px;
            height: 36px;
            background: rgba(255, 99, 99, 0.15);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .logo-text {
            font-size: 24px;
            font-weight: 700;
            color: #FFFFFF;
        }
        .content {
            background: #2A2A3E;
            border-radius: 14px;
            padding: 35px;
            border: 1px solid #33334A;
        }
        h1 {
            font-size: 20px;
            font-weight: 600;
            color: #FFFFFF;
            margin: 0 0 15px;
        }
        p {
            font-size: 14px;
            line-height: 1.6;
            color: #E4E4E4;
            margin: 0 0 20px;
        }
        .btn {
            display: inline-block;
            padding: 14px 32px;
            background: #FF6363;
            color: #FFFFFF;
            text-decoration: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            margin: 10px 0 20px;
        }
        .btn:hover {
            background: #e55555;
        }
        .footer {
            text-align: center;
            padding: 25px 0;
            font-size: 12px;
            color: #888899;
        }
        .brand {
            color: #FF6363;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">
                <div class="logo-icon">❤️</div>
                <span class="logo-text">CoFund</span>
            </div>
        </div>
        <div class="content">
            <h1>Halo, {{ $userName }}!</h1>
            <p>{{ $body }}</p>
            @if(isset($data['amount']))
                <p style="font-size: 18px; font-weight: 700; color: #FF6363;">
                    Rp {{ number_format($data['amount'], 0, ',', '.') }}
                </p>
            @endif
            @if(isset($data['action_label']) && isset($data['action_url']))
                <p style="text-align: center;">
                    <a href="{{ $data['action_url'] }}" class="btn">{{ $data['action_label'] }}</a>
                </p>
            @endif
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} CoFund. All rights reserved.</p>
            <p>Email ini dikirim secara otomatis, jangan membalas email ini.</p>
        </div>
    </div>
</body>
</html>
