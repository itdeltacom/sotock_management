<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Your Newsletter Subscription</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background-color: #0d6efd;
            padding: 20px;
            text-align: center;
        }

        .header h1 {
            color: white;
            margin: 0;
        }

        .content {
            padding: 20px;
            background-color: #f8f9fa;
        }

        .button {
            display: inline-block;
            background-color: #0d6efd;
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 4px;
            margin: 20px 0;
        }

        .footer {
            text-align: center;
            padding: 20px;
            font-size: 12px;
            color: #6c757d;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>Confirm Your Subscription</h1>
        </div>
        <div class="content">
            <p>Hello,</p>
            <p>Thank you for subscribing to our newsletter! Please confirm your subscription by clicking the button
                below:</p>

            <a href="{{ $confirmUrl }}" class="button">Confirm Subscription</a>

            <p>If you didn't request this subscription, you can safely ignore this email.</p>

            <p>If the button doesn't work, you can also copy and paste the following URL into your browser:</p>
            <p>{{ $confirmUrl }}</p>

            <p>Thank you,<br>Cental Car Rental Team</p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} Cental Car Rental. All rights reserved.</p>
            <p>This email was sent to {{ $subscriber->email }}</p>
        </div>
    </div>
</body>

</html>