@props([
    'url',
])
<tr>
    <td class="header">
        <a href="{{ $url }}">
            <img
                src="{{ asset('images/logo-fundacion-horizontal.png') }}"
                alt="{{ config('app.name') }}"
            />
        </a>
    </td>
</tr>
