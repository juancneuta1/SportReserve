<tr>
    <td class="header" style="padding: 0 0 18px 0;">
        <a href="{{ $url ?? config('app.url') }}"
            style="display: inline-flex; align-items:center; gap:8px; color: #0f2b1a; text-decoration: none; font-weight: 800; font-size: 18px;">
            <span
                style="background: #d1fadf; color:#0f8a3c; padding: 10px 14px; border-radius: 14px; font-weight: 800;">
                {{ \Illuminate\Support\Str::of(config('app.name', 'SR'))->substr(0, 2)->upper() }}
            </span>
            {{ config('app.name', 'SportReserve') }}
        </a>
    </td>
</tr>