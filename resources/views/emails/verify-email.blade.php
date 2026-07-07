<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Email - CoFund</title>
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
        .divider {
            border: none;
            border-top: 1px solid #33334A;
            margin: 25px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <span class="logo-text">CoFund</span>
        </div>
        <div class="content">
            <h1>Halo, {{ $userName }}!</h1>
            <p>Terima kasih telah mendaftar di CoFund. Silakan verifikasi alamat email Anda dengan mengklik tombol di bawah ini:</p>

            <p style="text-align: center;">
                <a href="{{ $verificationUrl }}" class="btn">Verifikasi Email</a>
            </p>

            <hr class="divider">

            <p style="font-size: 13px; color: #999AAA;">
                Jika Anda tidak membuat akun ini, abaikan email ini.<br>
                Tautan verifikasi akan kedaluwarsa dalam 60 menit.
            </p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} CoFund. All rights reserved.</p>
            <p>Email ini dikirim secara otomatis, jangan membalas email ini.</p>
        </div>
    </div>
</body>
</html>
