@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@php
    $emailSetting = \App\Models\EmailSetting::first();
    $defaultLogo = 'https://cdn.teamvora.web.id/gallery/2026/07/1783152710_icon.png';
    $logoUrl = $emailSetting && $emailSetting->logo_url ? $emailSetting->logo_url : $defaultLogo;
@endphp
<img src="{{ $logoUrl }}" class="logo" alt="{{ config('app.name') }} Logo" style="max-height: 50px;">
</a>
</td>
</tr>
