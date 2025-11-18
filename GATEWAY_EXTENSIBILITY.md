# Extensibilidad de Gateways de Pago - Payflow

## Introducción

El sistema de pagos ha sido diseñado con extensibilidad en mente, permitiendo agregar múltiples pasarelas de pago (
Redsys, Stripe, PayPal, etc.) de forma sencilla.

## Gateways Disponibles

### 1. Redsys (Predeterminado)

Gateway para pagos con tarjeta y Bizum a través de la pasarela española Redsys.

### 2. Stripe (Esqueleto)

Gateway preparado para integración con Stripe. Actualmente es un esqueleto funcional listo para implementación completa.

## Configuración

### Variables de Entorno

```env
# Gateway predeterminado (redsys, stripe, paypal)
PAYMENT_GATEWAY_DEFAULT=redsys

# Configuración Redsys
REDSYS_KEY=your_merchant_key
REDSYS_MERCHANT_CODE=your_merchant_code
REDSYS_TERMINAL=1
REDSYS_ENVIRONMENT=test

# Configuración Stripe (cuando esté implementado)
STRIPE_API_KEY=sk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...
```

### Archivo de Configuración

El archivo `config/payflow.php` permite configurar múltiples gateways:

```php
return [
    'default' => env('PAYMENT_GATEWAY_DEFAULT', 'redsys'),
    
    'gateways' => [
        'redsys' => [
            'key' => env('REDSYS_KEY'),
            'merchant_code' => env('REDSYS_MERCHANT_CODE'),
            // ...
        ],
        
        'stripe' => [
            'api_key' => env('STRIPE_API_KEY'),
            'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
        ],
    ],
];
```

## Uso

### Opción 1: Usar Gateway por Configuración

El gateway se selecciona automáticamente según `PAYMENT_GATEWAY_DEFAULT`:

```php
use App\Services\PaymentProcess;
use App\Models\Order;

// Usa el gateway configurado en payflow.default
$process = new PaymentProcess(Order::class, [
    'amount' => 50.00,
    'payment_method' => 'tarjeta',
]);

$paymentData = $process->getFormRedSysData();
```

### Opción 2: Usar Gateway Específico con Manager

```php
use Darkraul79\Payflow\Facades\Gateway;

// Obtener gateway Redsys explícitamente
$redsysGateway = app('gateway')->withRedsys();
$payment = $redsysGateway->createPayment(100.00, 'ORDER-001');

// Obtener gateway Stripe explícitamente
$stripeGateway = app('gateway')->withStripe();
$payment = $stripeGateway->createPayment(100.00, 'ORDER-002');
```

### Opción 3: Inyección Directa

```php
use Darkraul79\Payflow\Gateways\StripeGateway;
use App\Services\PaymentProcess;
use App\Models\Donation;

// Inyectar un gateway específico
$gateway = new StripeGateway();

$process = new PaymentProcess(
    Donation::class,
    ['amount' => 25.00],
    $gateway  // Gateway inyectado
);
```

## Añadir un Nuevo Gateway

### Paso 1: Crear la Clase del Gateway

```php
<?php

namespace Darkraul79\Payflow\Gateways;

use Darkraul79\Payflow\Contracts\GatewayInterface;

class PaypalGateway implements GatewayInterface
{
    public function createPayment(float $amount, string $orderId, array $options = []): array
    {
        // Implementación específica de PayPal
    }
    
    public function processCallback(array $data): array
    {
        // Procesar webhook de PayPal
    }
    
    public function verifySignature(array $data): bool
    {
        // Verificar firma de PayPal
    }
    
    public function getPaymentUrl(): string
    {
        return 'https://www.paypal.com/checkoutnow';
    }
    
    public function isSuccessful(array $data): bool
    {
        return ($data['status'] ?? '') === 'COMPLETED';
    }
    
    public function getErrorMessage(array $data): string
    {
        return $data['error_description'] ?? 'Error desconocido';
    }
    
    public function refund(string $transactionId, float $amount): bool
    {
        // Implementar reembolso
    }
    
    public function getName(): string
    {
        return 'paypal';
    }
}
```

### Paso 2: Registrar en el ServiceProvider

Editar `packages/payflow/src/PayflowServiceProvider.php`:

```php
public function register(): void
{
    $this->app->singleton('gateway', function ($app) {
        $manager = new PayflowManager;
        
        $manager->extend('redsys', fn () => new RedsysGateway);
        $manager->extend('stripe', fn () => new StripeGateway);
        $manager->extend('paypal', fn () => new PaypalGateway);  // Nuevo
        
        return $manager;
    });
}
```

### Paso 3: Añadir Configuración

Editar `packages/payflow/config/payflow.php`:

```php
'gateways' => [
    // ...existing gateways...
    
    'paypal' => [
        'client_id' => env('PAYPAL_CLIENT_ID'),
        'client_secret' => env('PAYPAL_CLIENT_SECRET'),
        'mode' => env('PAYPAL_MODE', 'sandbox'),
    ],
],
```

### Paso 4: Añadir Método de Conveniencia (Opcional)

Editar `packages/payflow/src/PayflowManager.php`:

```php
public function withPaypal(): GatewayInterface
{
    return $this->gateway('paypal');
}
```

## Tests

Ejemplos de tests para verificar la selección de gateway:

```php
test('puedo usar Stripe como gateway', function () {
    config(['payflow.default' => 'stripe']);
    
    $process = new PaymentProcess(Order::class, ['amount' => 50.00]);
    
    // Verificar que usa Stripe internamente
    $reflection = new ReflectionClass($process);
    $property = $reflection->getProperty('gateway');
    $property->setAccessible(true);
    
    expect($property->getValue($process))->toBeInstanceOf(StripeGateway::class);
});
```

## Interfaz GatewayInterface

Todos los gateways deben implementar esta interfaz:

```php
interface GatewayInterface
{
    public function createPayment(float $amount, string $orderId, array $options = []): array;
    public function processCallback(array $data): array;
    public function verifySignature(array $data): bool;
    public function getPaymentUrl(): string;
    public function isSuccessful(array $data): bool;
    public function getErrorMessage(array $data): string;
    public function refund(string $transactionId, float $amount): bool;
    public function getName(): string;
}
```

## Estado Actual

### ✅ Redsys

- **Estado**: Completamente implementado y funcional
- **Características**: Pagos únicos, pagos recurrentes, Bizum, verificación de firma
- **Tests**: Suite completa de tests

### ⚙️ Stripe

- **Estado**: Esqueleto funcional
- **Características**: Estructura básica implementada
- **Pendiente**: Integración completa con Stripe API, webhooks, Payment Intents

### 📋 PayPal

- **Estado**: No implementado
- **Pendiente**: Crear clase, registrar en provider, configuración

## Próximos Pasos

Para implementar completamente Stripe:

1. Instalar SDK de Stripe: `composer require stripe/stripe-php`
2. Implementar `createPayment()` con Payment Intents
3. Implementar `processCallback()` para webhooks
4. Implementar `verifySignature()` con webhook secret
5. Crear tests de integración
6. Documentar flujo específico de Stripe

## Ventajas de esta Arquitectura

- ✅ **Extensible**: Añadir nuevos gateways es sencillo
- ✅ **Flexible**: Cambiar gateway por configuración sin modificar código
- ✅ **Testeable**: Fácil de mockear y testear
- ✅ **Mantenible**: Cada gateway es independiente
- ✅ **Compatible**: No rompe funcionalidad existente

