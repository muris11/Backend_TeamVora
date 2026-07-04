<x-mail::layout>
{{-- Header --}}
<x-slot:header>
<x-mail::header :url="config('app.url')">
{{ config('app.name') }}
</x-mail::header>
</x-slot:header>

{{-- Body --}}
{!! $slot !!}

{{-- Subcopy --}}
@isset($subcopy)
<x-slot:subcopy>
<x-mail::subcopy>
{!! $subcopy !!}
</x-mail::subcopy>
</x-slot:subcopy>
@endisset

@php
    $emailSetting = \App\Models\EmailSetting::first();
    $primaryColor = $emailSetting && $emailSetting->primary_color ? $emailSetting->primary_color : '#0284c7';
    $footerText = $emailSetting && $emailSetting->footer_text ? $emailSetting->footer_text : '© ' . date('Y') . ' ' . config('app.name') . '. ' . __('All rights reserved.');
@endphp
<style>
    .button-primary { background-color: {{ $primaryColor }} !important; border-color: {{ $primaryColor }} !important; }
    .button-primary:hover { background-color: {{ $primaryColor }} !important; filter: brightness(0.9); }
    a { color: {{ $primaryColor }} !important; }
</style>

{{-- Footer --}}
<x-slot:footer>
<x-mail::footer>
{!! nl2br(e($footerText)) !!}
</x-mail::footer>
</x-slot:footer>
</x-mail::layout>
