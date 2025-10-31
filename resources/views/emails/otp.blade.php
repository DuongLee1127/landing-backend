<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f7f9fc;
            color: #333;
            padding: 20px;
        }

        .container {
            max-width: 480px;
            margin: auto;
            background: #fff;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            font-weight: bold;
            color: #2d89ef;
            font-size: 22px;
        }

        .otp {
            text-align: center;
            font-size: 32px;
            letter-spacing: 5px;
            font-weight: bold;
            color: #2d89ef;
            margin: 20px 0;
        }

        .note {
            font-size: 14px;
            color: #777;
            text-align: center;
        }

        .footer {
            text-align: center;
            font-size: 12px;
            color: #999;
            margin-top: 30px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">🔐 Dương gửi OTP</div>

        <p>Xin chào,</p>
        <p>Cảm ơn bạn đã sử dụng dịch vụ của chúng tôi.</p>
        <p><strong>Mã OTP của bạn là:</strong></p>

        <div class="otp">{{ $otp }}</div>

        <p style="text-align:center;">Mã này sẽ hết hạn sau <strong>5 phút</strong>.</p>

        <p class="note">
            Nếu bạn không yêu cầu mã này, vui lòng bỏ qua email này.
        </p>

        <div class="footer">
            © {{ date('Y') }} Duongleee. All rights reserved.
        </div>
    </div>
</body>

</html>