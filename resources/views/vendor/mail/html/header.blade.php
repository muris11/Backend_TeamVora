@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@php
    $emailSetting = \App\Models\EmailSetting::first();
    $logoUrl = $emailSetting && $emailSetting->logo_url ? $emailSetting->logo_url : 'https://cdnteamvora.center.biz.id/teamvora/icon.png';
@endphp
<img src="{{ $logoUrl }}" class="logo" alt="{{ config('app.name') }} Logo" style="max-height: 50px;">
</a>
</td>
</tr>
