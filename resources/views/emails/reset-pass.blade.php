<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Your Password</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f7f8fb;
        }

        .email-wrapper {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .email-header {
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 1px solid #e2e5f1;
        }

        .email-header img {
            max-width: 150px;
            height: auto;
        }

        .email-body {
            padding: 30px 0;
        }

        .email-footer {
            text-align: center;
            font-size: 12px;
            color: #8A8BB3;
            padding-top: 20px;
            border-top: 1px solid #e2e5f1;
        }

        h1 {
            color: #25265E;
            font-size: 24px;
            margin: 0 0 20px;
            font-weight: 600;
        }

        p {
            margin: 0 0 20px;
            font-size: 16px;
        }

        .button {
            display: inline-block;
            background: linear-gradient(to right, #2D3FE0, #5468FF);
            color: #ffffff !important;
            text-decoration: none;
            padding: 12px 25px;
            border-radius: 6px;
            font-weight: 600;
            margin: 20px 0;
            box-shadow: 0 4px 12px rgba(45, 63, 224, 0.25);
        }

        .button:hover {
            background: linear-gradient(to right, #5468FF, #2D3FE0);
        }

        .reset-link {
            word-break: break-all;
            color: #2D3FE0;
            font-size: 14px;
        }

        .expiry-notice {
            font-size: 14px;
            color: #5F6188;
            font-style: italic;
            margin-top: 30px;
        }

        .help-text {
            font-size: 14px;
            color: #5F6188;
            margin-top: 30px;
        }
    </style>
</head>

<body>
    <div class="email-wrapper">
        <div class="email-header">
            <img src="{{ asset('img/logo.png') }}" alt="BATI Car Rental">
        </div>
        <div class="email-body">
            <h1>Reset Your Password</h1>
            <p>Hello,</p>
            <p>You are receiving this email because we received a password reset request for your account on the BATI
                Car Rental Admin Panel.</p>
            <p>Please click the button below to reset your password:</p>
            <div style="text-align: center;">
                <a href="{{ $resetLink }}" class="button">Reset Password</a>
            </div>
            <p>If you're having trouble clicking the button, copy and paste the URL below into your web browser:</p>
            <p class="reset-link">{{ $resetLink }}</p>
            <p class="expiry-notice">This password reset link will expire in 60 minutes.</p>
            <p class="help-text">If you did not request a password reset, no further action is required. Your account
                remains secure.</p>
        </div>
        <div class="email-footer">
            <p>&copy; {{ date('Y') }} BATI Car Rental. All rights reserved.</p>
            <p>This is an automated message, please do not reply.</p>
        </div>
    </div>
</body>

</html>