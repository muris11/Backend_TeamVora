@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@php
    $settings = \App\Models\Setting::getByGroup('email');
    $generalSettings = \App\Models\Setting::getByGroup('general');
    $defaultLogo = 'https://cdn.teamvora.web.id/gallery/2026/07/1783152710_icon.png';
    $logoUrl = $settings['email_logo_url'] ?? ($generalSettings['org_logo_url'] ?? $defaultLogo);
@endphp
<img src="{{ $logoUrl }}" class="logo" alt="{{ config('app.name') }} Logo" style="max-height: 50px;">
</a>
</td>
</tr>
