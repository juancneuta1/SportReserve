@props([
    'url',
    'color' => 'primary',
])
<table class="action" align="center" width="100%" cellpadding="0" cellspacing="0" role="presentation">
<tr>
    <td align="center">
        <table width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation">
        <tr>
            <td align="center">
                <table border="0" cellpadding="0" cellspacing="0" role="presentation">
                    <tr>
                        <td>
                            <a href="{{ $url }}" class="button button-{{ $color }}" target="_blank"
                               style="border-radius: 14px; background: linear-gradient(145deg, #16a34a, #128a3e); border: 1px solid #128a3e; color: #ffffff; padding: 12px 26px; font-weight: 700; letter-spacing: 0.2px; text-decoration: none; display: inline-block; box-shadow: 0 10px 24px rgba(22, 163, 74, 0.25);">
                                {{ $slot }}
                            </a>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        </table>
    </td>
</tr>
</table>
