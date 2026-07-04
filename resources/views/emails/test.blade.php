@extends('emails.layout')

@section('content')
    <h1>Test Email TeamVora</h1>
    <p>Halo,</p>
    <p>Ini adalah email percobaan untuk memastikan bahwa konfigurasi SMTP Anda di TeamVora berjalan dengan baik. Jika Anda menerima email ini, berarti sistem pengiriman email telah berfungsi dengan normal.</p>
    <p>Anda dapat mengabaikan email ini atau menghapusnya jika tidak diperlukan.</p>
    
    <div style="text-align: center;">
        <a href="{{ url('/') }}" class="button">Kembali ke TeamVora</a>
    </div>
@endsection
