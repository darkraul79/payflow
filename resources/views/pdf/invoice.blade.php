@php use App\Models\Order; @endphp
@php use App\Models\Donation; @endphp
    <!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura {{ $invoice->number }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #111;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }

        .logo {
            height: 48px;
        }

        .title {
            font-size: 18px;
            font-weight: bold;
        }

        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .box {
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 6px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            border: 1px solid #e5e5e5;
            padding: 8px;
            text-align: left;
        }

        th {
            background: #f8f8f8;
        }

        .right {
            text-align: right;
        }

        .totals {
            margin-top: 12px;
            width: 60%;
            margin-left: auto;
        }

        .muted {
            color: #666;
            font-size: 11px;
        }

        .footer {
            margin-top: 28px;
            border-top: 1px solid #eee;
            padding-top: 8px;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
<div class="header">
    <div>
        @php
            $logoPath = $settings['logo_abs_path'] ?? '';
            $logoSvg = $settings['logo_svg_content'] ?? '';
            $logoDataUri = $settings['logo_data_uri'] ?? '';
        @endphp
        @if (!empty($logoSvg))
            <div class="logo" style="height:48px; width:auto;">
                {!! $logoSvg !!}
            </div>
        @elseif (!empty($logoDataUri))
            <img class="logo" src="{{ $logoDataUri }}" alt="Logo" />
        @elseif (!empty($logoPath))
            <img class="logo" src="file://{{ $logoPath }}" alt="Logo" />
        @elseif (file_exists(public_path('images/logo-fundacion-horizontal.svg')))
            @php $fallback = file_get_contents(public_path('images/logo-fundacion-horizontal.svg')); @endphp
            <div class="logo" style="height:48px; width:auto;">{!! $fallback !!}</div>
        @elseif (file_exists(public_path('images/logo-fundacion-horizontal.png')))
            <img class="logo" src="file://{{ public_path('images/logo-fundacion-horizontal.png') }}" alt="Logo" />
        @endif
    </div>
    <div>
        <div class="title">Factura {{ $invoice->number }}</div>
        <div class="muted">Fecha: {{ $invoice->created_at->format('d/m/Y') }}</div>
    </div>
</div>

<div class="grid">
    <div class="box">
        <strong>Emisor</strong>
        <div>{{ $settings['company'] ?? '' }} ({{ $settings['nif'] ?? '' }})</div>
        <div>{{ $settings['address'] ?? '' }}</div>
        <div>{{ $settings['postal_code'] ?? '' }} {{ $settings['city'] ?? '' }} ({{ $settings['country'] ?? '' }})</div>
        <div>{{ $settings['email'] ?? '' }} {{ ($settings['phone'] ?? null) ? ' · '.($settings['phone'] ?? '') : '' }}</div>
    </div>
    <div class="box">
        <strong>Cliente</strong>
        @php
            $clientName = '';
            $clientEmail = '';
            if ($invoiceable instanceof Order) {
                $addr = $invoiceable->billing_address();
                $clientName = $addr?->name ?? '';
                $clientEmail = $addr?->email ?? '';
            } elseif ($invoiceable instanceof Donation) {
                $addr = $invoiceable->certificate() ?: null; // ensure null if false
                $clientName = $addr?->name ?? '';
                $clientEmail = $addr?->email ?? '';
            }
        @endphp
        <div>{{ $clientName }}</div>
        <div>{{ $clientEmail }}</div>
    </div>
</div>

<table>
    <thead>
    <tr>
        <th>Concepto</th>
        <th class="right">Cantidad</th>
        <th class="right">Precio</th>
        <th class="right">Importe</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($lines as $line)
        <tr>
            <td>{{ $line['name'] }}</td>
            <td class="right">{{ number_format($line['quantity'], 0) }}</td>
            <td class="right">{{ number_format($line['unit_price'], 2, ',', '.') }} €</td>
            <td class="right">{{ number_format($line['line_total'], 2, ',', '.') }} €</td>
        </tr>
    @endforeach
    @if (($meta['shipping_cost'] ?? 0) > 0)
        <tr>
            <td>Gastos de envío</td>
            <td class="right">1</td>
            <td class="right">{{ number_format($meta['shipping_cost'], 2, ',', '.') }} €</td>
            <td class="right">{{ number_format($meta['shipping_cost'], 2, ',', '.') }} €</td>
        </tr>
    @endif
    </tbody>
</table>

<table class="totals">
    <tr>
        <td>Base imponible</td>
        <td class="right">{{ number_format($subtotal, 2, ',', '.') }} €</td>
    </tr>
    <tr>
        <td>IVA ({{ number_format($vatRate * 100, 2, ',', '.') }}%)</td>
        <td class="right">{{ number_format($vatAmount, 2, ',', '.') }} €</td>
    </tr>
    <tr>
        <td><strong>Total</strong></td>
        <td class="right"><strong>{{ number_format($total, 2, ',', '.') }} €</strong></td>
    </tr>
</table>

<div class="footer">
    Esta factura se emite automáticamente. Para cualquier consulta puede contactar con nosotros en {{ $settings['email'] ?? '' }}.
    En caso de donaciones, el importe podría estar exento de IVA según la normativa vigente.
</div>
</body>
</html>
