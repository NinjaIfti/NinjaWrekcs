@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if (trim($slot) === 'Laravel' || trim($slot) === 'NinjaWrecks' || trim($slot) === config('app.name'))
<div style="font-size: 32px; font-weight: bold; color: #8b5cf6; text-shadow: 2px 2px 4px rgba(139, 92, 246, 0.3); letter-spacing: 2px; font-family: Arial, sans-serif;">
    🎮 NINJAWRECKS
</div>
@else
{!! $slot !!}
@endif
</a>
</td>
</tr>
