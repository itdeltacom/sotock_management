<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Your Admin Password</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            color: #333;
        }

        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            padding-bottom: 20px;
        }

        .header img {
            max-width: 150px;
        }

        .content {
            line-height: 1.6;
        }

        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #2D3FE0;
            color: #ffffff;
            text-decoration: none;
            border-radius: 4px;
            margin: 20px 0;
            text-align: center;
        }

        .footer {
            text-align: center;
            padding-top: 20px;
            font-size: 12px;
            color: #777;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <img src="{{ asset('img/logo.png') }}" alt="Logo">
            <h2>Reset Your Admin Password</h2>
        </div>
        <div class="content">
            <p>Hello,</p>
            <p>We received a request to reset your admin password. Click the button below to set a new password:</p>
            <p style="text-align: center;">
                <a href="{{ $resetLink }}" class="button">Reset Password</a>
            </p>
            <p>If you did not request a password reset, please ignore this email or contact support.</p>
            <p>This link will expire in 60 minutes.</p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} Your Application Name. All rights reserved.</p>
            <p>If you have any questions, contact us at support@yourdomain.com.</p>
        </div>
    </div>
</body>

</html>