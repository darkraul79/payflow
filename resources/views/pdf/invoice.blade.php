@php use App\Models\Order; @endphp
@php use App\Models\Donation; @endphp
    <!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura {{ $invoice->number }}</title>
    <style>
        body {
            font-family: Arial, DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #111;
        }

        h1 {
            font-size: 15px;
            font-weight: bold;
            text-transform: uppercase;
            color: #111;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }

        .logo {
            max-height: 120px;
        }


        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }


        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            border: 1px solid #083c61;
            padding: 8px;
            text-align: left;
        }

        th {
            background: #aed1da;
        }

        .right {
            text-align: right;
        }

        .totals {
            margin-top: 30px;
            width: 50%;
            margin-left: auto;
        }


        .footer {
            margin-top: 28px;
            padding-top: 8px;
            text-align: right;
        }

        .total {
            font-weight: bolder;
            text-transform: uppercase;
            background: #88acb5;
        }
    </style>
</head>
<body>
<div class="header">
    <div class="grid">
        <div style="margin-right: 50px;">
            @php
                $logoPath = $settings['logo_abs_path'] ?? '';
                $logoSvg = $settings['logo_svg_content'] ?? '';
                $logoDataUri = $settings['logo_data_uri'] ?? '';

            @endphp
            @if (!empty($logoSvg))
                <div class="logo" style=" width:auto;">
                    {!! $logoSvg !!}
                </div>
            @elseif (!empty($logoDataUri))
                <img class="logo" src="{{ $logoDataUri }}" alt="Logo" />
            @elseif (!empty($logoPath))
                <img class="logo" src="file://{{ $logoPath }}" alt="Logo" />
            @elseif (file_exists(public_path('images/logo-fundacion-horizontal.svg')))
                @php $fallback = file_get_contents(public_path('images/logo-fundacion-horizontal.svg')); @endphp
                <div class="logo" style=" width:auto;">{!! $fallback !!}</div>
            @elseif (file_exists(public_path('images/logo-fundacion-horizontal.png')))
                <img class="logo" src="file://{{ public_path('images/logo-fundacion-horizontal.png') }}" alt="Logo" />
            @endif
        </div>
        <div>
            <h1>{{ $settings['company'] ?? '' }}</h1>
            <div>{{ $settings['address'] ?? '' }}. {{ $settings['postal_code'] ?? '' }} {{ $settings['city'] ?? '' }} ({{ $settings['country'] ?? '' }})</div>
            <div>CIF: {{ $settings['nif'] ?? '' }}</div>
            @if(isset($settings['phone']) && $settings['phone']!= '')
                <div>Tel. {{ $settings['phone'] }}</div>
            @endif
            @if(isset($settings['email']) && $settings['email']!= '')
                <div><a href="mailto:{{ $settings['email'] }}" target="_blank">{{ $settings['email'] }}</a></div>
            @endif
        </div>

    </div>
</div>
<div style="text-align: right; width: 100%;margin: 40px 0 10px 0;">
    <div class=""><strong>FACTURA SOLIDARIA</strong> Nº {{ $invoice->number }}</div>
    <div class=""><strong>Fecha:</strong> {{ $invoice->created_at->format('d/m/Y') }}</div>
</div>

<div class="grid">
    <div>
        @php
            $clientName = '';
            $clientEmail = '';
            if ($invoiceable instanceof Order) {
                $addr = $invoiceable->billing_address();
            } elseif ($invoiceable instanceof Donation) {
                $addr = $invoiceable->certificate() ?: null; // ensure null if false
            }

            $clientName = $addr?->full_name ?? '';
            $clientEmail = $addr?->email ?? '';
            $clientCif = $addr?->nif ?? '';
            $clientCompany = $addr?->company ?? '';
            $clientAddress = $addr?->getFullAddress();
            $clientPhone = $addr?->phone ?? '';
        @endphp

        @if($clientCompany != '')
            <div><strong>{{ $clientCompany }}</strong></div>
        @endif
        <div>{{ $clientName }}</div>
        @if($clientCif != '')
            <div>DNI/CIF: {{ $clientCif }}</div>
        @endif
        <div>{{$clientAddress}}</div>
        @if($clientEmail != '')
            <div><a href="mailto:{{ $clientEmail }}" target="_blank">{{ $clientEmail }}</a></div>
        @endif
        <div>Teléfono: {{$clientPhone}}</div>
    </div>
</div>

<table>
    <thead>
    <tr>
        <th>Descripción</th>
        <th class="right">Cantidad</th>
        <th class="right">Precio unitario (€)</th>
        <th class="right">Total (€)</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($lines as $line)
        <tr>
            <td>{{ $line['name'] }}</td>
            <td class="right">{{ $line['quantity'] }}</td>
            <td class="right">{{ number_format($line['unit_price'], 2, ',', '.') }} </td>
            <td class="right">{{ number_format($line['line_total'], 2, ',', '.') }} </td>
        </tr>
    @endforeach

    </tbody>
</table>

<table class="totals">
    @if (($meta['shipping_cost'] ?? 0) > 0)
        <tr>
            <td>Gastos de envío: {{ $meta['shipping_method'] }}</td>
            <td class="right">{{ number_format($meta['shipping_cost'], 2, ',', '.') }} €</td>
        </tr>
    @endif
    <tr>
        <td>Base imponible</td>
        <td class="right">{{ number_format($subtotal, 2, ',', '.') }} €</td>
    </tr>
    <tr>
        <td>IVA ({{ number_format($vatRate * 100, 2, ',', '.') }}%)</td>
        <td class="right">{{ number_format($vatAmount, 2, ',', '.') }} €</td>
    </tr>
    <tr>
        <td class="total"><strong>Total</strong></td>
        <td class="right total"><strong>{{ number_format($total, 2, ',', '.') }} €</strong></td>
    </tr>
</table>
<div class="grid">
    @php
        $paymentMethod = $invoiceable->payment_method ?? '';
    @endphp
    @if($paymentMethod != '')
        <div style="margin: 20px 0;">
            Forma de pago: <span style="text-transform:capitalize;">{{ $paymentMethod }}</span>
        </div>
    @endif
</div>

<div class="footer ">
    Gracias por tu compra solidaria.
</div>
</body>
</html>
