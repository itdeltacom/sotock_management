@component('mail::message')
# {{ $subject ?? 'Notification from ' . $companyName }}

Dear {{ $clientName }},

{{ $messageContent }}

If you have any questions, please contact our support team at support@{{ config('app.url') }}.

Best regards,
The {{ $companyName }} Team

@component('mail::subcopy')
This is an automated message. Please do not reply directly to this email.
@endcomponent

@component('mail::layout')
@slot('header')
@component('mail::header', ['url' => config('app.url')])
<img src="{{ $logoUrl }}" alt="{{ $companyName }} Logo" style="max-height: 50px;">
@endcomponent
@endslot

@slot('footer')
@component('mail::footer')
&copy; {{ $year }} {{ $companyName }}. All rights reserved.
@endcomponent
@endslot
@endcomponent