@extends('emails.layout')

@section('content')
    <h1>Reset Password TeamVora</h1>
    <p>Halo,</p>
    <p>Anda menerima email ini karena kami menerima permintaan reset password untuk akun Anda.</p>
    
    <div style="text-align: center;">
        <a href="{{ $resetLink }}" class="button">Reset Password</a>
    </div>

    <p style="margin-top: 24px;">Link reset password ini akan kedaluwarsa dalam 60 menit.</p>
    <p>Jika Anda tidak meminta reset password, abaikan email ini.</p>
    
    <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 32px 0;">
    <p style="font-size: 12px; color: #6b7280; text-align: left;">
        Jika Anda mengalami masalah saat mengklik tombol "Reset Password", salin dan tempel URL di bawah ini ke browser web Anda:
        <br>
        <a href="{{ $resetLink }}" style="word-break: break-all; color: #2563eb;">{{ $resetLink }}</a>
    </p>
@endsection
