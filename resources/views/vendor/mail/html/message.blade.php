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
    $settings = \App\Models\Setting::getByGroup('email');
    
    $colorMap = [
        'blue' => '#3b82f6', 'sky' => '#0ea5e9', 'indigo' => '#6366f1',
        'violet' => '#8b5cf6', 'purple' => '#a855f7', 'fuchsia' => '#d946ef',
        'pink' => '#ec4899', 'rose' => '#f43f5e', 'red' => '#ef4444',
        'orange' => '#f97316', 'amber' => '#f59e0b', 'yellow' => '#eab308',
        'lime' => '#84cc16', 'green' => '#22c55e', 'emerald' => '#10b981',
        'teal' => '#14b8a6', 'cyan' => '#06b6d4', 'slate' => '#64748b',
        'gray' => '#6b7280', 'zinc' => '#71717a', 'primary' => '#0f172a'
    ];
    
    $rawColor = $settings['email_button_color'] ?? ($settings['email_primary_color'] ?? 'blue');
    $primaryColor = $colorMap[$rawColor] ?? (str_starts_with($rawColor, '#') ? $rawColor : '#3b82f6');
    
    $footerText = $settings['email_footer_text'] ?? '© ' . date('Y') . ' ' . config('app.name') . '. Hak Cipta Dilindungi.';
@endphp
<style>
    .button-primary { background-color: {{ $primaryColor }} !important; border-color: {{ $primaryColor }} !important; color: #ffffff !important; }
    .button-primary:hover { background-color: {{ $primaryColor }} !important; filter: brightness(0.9); }
    a:not(.button-primary) { color: {{ $primaryColor }} !important; }
</style>

{{-- Footer --}}
<x-slot:footer>
<x-mail::footer>
{!! nl2br(e($footerText)) !!}
</x-mail::footer>
</x-slot:footer>
</x-mail::layout>
