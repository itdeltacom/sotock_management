<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $newsletter->subject }}</title>
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

        .unsubscribe {
            font-size: 11px;
            color: #6c757d;
            text-align: center;
            margin-top: 15px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>{{ $newsletter->subject }}</h1>
        </div>
        <div class="content">
            {!! $content !!}
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} Cental Car Rental. All rights reserved.</p>
            <div class="unsubscribe">
                <p>This email was sent to {{ $subscriber->email }}</p>
                <p>If you no longer wish to receive these emails, you can <a href="{{ $unsubscribeUrl }}">unsubscribe
                        here</a>.</p>
            </div>
        </div>
    </div>
</body>

</html>