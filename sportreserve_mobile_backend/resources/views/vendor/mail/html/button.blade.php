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
                               style="border-radius: 12px; background: #16a34a; border: 1px solid #16a34a; color: #ffffff; padding: 12px 24px; font-weight: 700; letter-spacing: 0.2px; text-decoration: none; display: inline-block;">
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
