@extends('emails.layout')

@section('content')
    <h1>Undangan Bergabung Tim: {{ $invitation->team->name }}</h1>
    <p>Halo,</p>
    <p>Anda diundang untuk bergabung ke tim <strong>{{ $invitation->team->name }}</strong> di TeamVora.</p>
    <p>Undangan ini dikirim oleh: <strong>{{ $invitation->inviter->name }}</strong></p>
    
    <div style="text-align: center;">
        <a href="{{ $acceptUrl }}" class="button">Terima Undangan</a>
    </div>

    <p style="margin-top: 24px; font-size: 14px; color: #6b7280;">
        Undangan ini berlaku hingga {{ $invitation->expires_at->setTimezone('Asia/Jakarta')->translatedFormat('d M Y H:i') }} WIB.<br>
        Jika Anda tidak merasa diundang, Anda dapat mengabaikan email ini.
    </p>
@endsection
