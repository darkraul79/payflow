# Payflow

<p align="center">
  <strong>A flexible multi-gateway payment package for Laravel</strong>
</p>

<p align="center">
  <a href="https://packagist.org/packages/darkraul79/payflow"><img src="https://img.shields.io/packagist/v/darkraul79/payflow" alt="Latest Version"></a>
  <a href="https://packagist.org/packages/darkraul79/payflow"><img src="https://img.shields.io/packagist/dt/darkraul79/payflow" alt="Total Downloads"></a>
  <a href="https://packagist.org/packages/darkraul79/payflow"><img src="https://img.shields.io/packagist/l/darkraul79/payflow" alt="License"></a>
</p>

> ⚠️ **Alpha Version (0.1.x)** - This package is in early development. APIs may change. Use with caution in production.

---

## Features

- ✅ Unified API for multiple payment gateways
- ✅ **Redsys fully implemented** (Spain's leading payment gateway)
- ✅ Bizum support (instant mobile payments)
- ✅ Recurring payments
- ✅ Automatic signature verification
- ✅ Transaction logging to database
- ✅ Refund management
- ✅ Easy to extend with new gateways
- ✅ Laravel 12+ support

---

## Supported Gateways

- ✅ **Redsys** (Production ready)
- 🚧 **Stripe** (Coming soon)
- 🚧 **PayPal** (Coming soon)

---

## Installation

```bash
composer require darkraul79/payflow
```

Publish configuration and migrations:

```bash
php artisan vendor:publish --provider="Raulsdev\Payflow\PayflowServiceProvider"
```

Run migrations:

```bash
php artisan migrate
```

---

## Configuration

Add to your `.env`:

```env
# Default Gateway
PAYMENT_GATEWAY_DEFAULT=redsys

# Redsys Configuration
REDSYS_KEY=your-secret-key
REDSYS_MERCHANT_CODE=your-merchant-code
REDSYS_TERMINAL=1
REDSYS_CURRENCY=978
REDSYS_ENVIRONMENT=test
REDSYS_TRADE_NAME="Your Store"

# Stripe (optional)
STRIPE_API_KEY=your-stripe-key
STRIPE_WEBHOOK_SECRET=your-webhook-secret
```

---

## Quick Start

```php
use Darkraul79\Payflow\Facades\Gateway;

// Create payment
$payment = Gateway::withRedsys()->createPayment(
    amount: 100.50,
    orderId: 'ORDER-123',
    options: [
        'url_ok' => route('payment.success'),
        'url_ko' => route('payment.error'),
        'url_notification' => route('payment.callback'),
    ]
);

// Process callback
$result = Gateway::withRedsys()->processCallback($request->all());

if (Gateway::withRedsys()->isSuccessful($request->all())) {
    // Payment successful
    $data = $result['decoded_data'];
    $amount = convert_amount_from_redsys($data['Ds_Amount']);
}
```

---

## Usage

### Creating Payments

#### Basic Payment

```php
$payment = Gateway::withRedsys()->createPayment(
    amount: 100.50,
    orderId: 'ORDER-123',
    options: [
        'url_ok' => route('payment.success'),
        'url_ko' => route('payment.error'),
    ]
);

// Returns:
// [
//     'Ds_MerchantParameters' => '...',
//     'Ds_Signature' => '...',
//     'Ds_SignatureVersion' => 'HMAC_SHA256_V1',
//     'form_url' => 'https://sis.redsys.es/...',
//     'raw_parameters' => [...],
// ]
```

#### Bizum Payment

```php
$payment = Gateway::withRedsys()->createPayment(
    amount: 50.00,
    orderId: 'ORDER-124',
    options: [
        'payment_method' => 'bizum',
        'url_ok' => route('payment.success'),
        'url_ko' => route('payment.error'),
    ]
);
```

#### Recurring Payment

```php
// First payment (register card)
$payment = Gateway::withRedsys()->createPayment(
    amount: 29.99,
    orderId: 'ORDER-125',
    options: [
        'recurring' => [
            'identifier' => 'REQUIRED',
            'cof_ini' => 'S',
            'cof_type' => 'R',
        ],
    ]
);

// Subsequent automatic payments
$payment = Gateway::withRedsys()->createPayment(
    amount: 29.99,
    orderId: 'ORDER-126',
    options: [
        'recurring' => [
            'identifier' => $savedIdentifier,
            'cof_txnid' => $savedTxnid,
            'excep_sca' => 'MIT',
            'direct_payment' => 'true',
        ],
    ]
);
```

### Processing Callbacks

```php
public function handleCallback(Request $request)
{
    // Process gateway response
    $result = Gateway::withRedsys()->processCallback($request->all());
    
    // Verify signature
    if (!Gateway::withRedsys()->verifySignature($request->all())) {
        abort(403, 'Invalid signature');
    }
    
    // Check if successful
    if (Gateway::withRedsys()->isSuccessful($request->all())) {
        $data = $result['decoded_data'];
        $orderId = $data['Ds_Order'];
        $amount = convert_amount_from_redsys($data['Ds_Amount']);
        
        // Update your order...
        
        return redirect()->route('payment.success');
    }
    
    // Payment failed
    $error = Gateway::withRedsys()->getErrorMessage($request->all());
    return redirect()->route('payment.error')->with('error', $error);
}
```

### Display Payment Form

```blade
<form action="{{ $payment['form_url'] }}" method="POST">
    <input type="hidden" name="Ds_SignatureVersion" value="{{ $payment['Ds_SignatureVersion'] }}">
    <input type="hidden" name="Ds_MerchantParameters" value="{{ $payment['Ds_MerchantParameters'] }}">
    <input type="hidden" name="Ds_Signature" value="{{ $payment['Ds_Signature'] }}">
    
    <button type="submit">Pay Now</button>
</form>
```

---

## API Reference

### Gateway Methods

```php
// Get gateway instance
Gateway::gateway(?string $name = null): GatewayInterface

// Shortcuts
Gateway::withRedsys(): GatewayInterface
Gateway::withStripe(): GatewayInterface
Gateway::withPaypal(): GatewayInterface
```

### Gateway Interface

```php
createPayment(float $amount, string $orderId, array $options = []): array
processCallback(array $data): array
verifySignature(array $data): bool
getPaymentUrl(): string
isSuccessful(array $data): bool
getErrorMessage(array $data): string
refund(string $transactionId, float $amount): bool
getName(): string
```

### Helper Functions

```php
// Get gateway
$gateway = gateway('redsys');

// Convert amounts
convert_amount_to_redsys(100.50); // "10050"
convert_amount_from_redsys("10050"); // 100.50
```

---

## Adding Custom Gateways

```php
use Raulsdev\Payflow\Contracts\GatewayInterface;

class MyGateway implements GatewayInterface
{
    public function createPayment(float $amount, string $orderId, array $options = []): array
    {
        // Your implementation
    }
    
    // Implement other methods...
}

// Register in a service provider
use Raulsdev\Payflow\Facades\Gateway;

Gateway::extend('mygateway', fn() => new MyGateway());

// Use it
Gateway::withMygateway()->createPayment(100.50, 'ORDER-123');
```

---

## Database Schema

The package includes migrations for:

### `gateway_transactions` table

- Transaction logging
- Gateway name, transaction ID, order ID
- Amount, currency, status
- Gateway request/response data
- Timestamps

### `gateway_refunds` table

- Refund tracking
- Links to transactions
- Refund amount, reason, status
- Gateway response data

---

## Redsys Error Codes

The package includes human-readable error messages for all Redsys error codes:

- `0101` - Expired card
- `0102` - Card under suspicion of fraud
- `0116` - Insufficient funds
- `0129` - Incorrect CVV
- `0190` - Denied without reason
- And many more...

---

## Testing

```bash
composer test
```

---

## Security

- All gateway responses are automatically verified
- Signatures are checked before processing
- Sensitive data is stored encrypted
- HTTPS required for production

---

## Contributing

Contributions are welcome! Please submit Pull Requests for:

- New gateway implementations
- Bug fixes
- Documentation improvements
- Tests

---

## License

MIT License. See [LICENSE](LICENSE) for details.

---

## Credits

- [Raul Sebastian (darkraul79)](https://github.com/darkraul79)
- Redsys implementation based on official documentation

---

## Support

If you find this package helpful, please ⭐ star the repository!

For issues or questions, open an issue on [GitHub](https://github.com/darkraul79/payflow).

---

## Roadmap

- [ ] Complete Stripe implementation
- [ ] Complete PayPal implementation
- [ ] Add more gateways (Square, Braintree, etc.)
- [ ] Enhanced refund management
- [ ] Webhook management
- [ ] Admin dashboard
- [ ] More tests

