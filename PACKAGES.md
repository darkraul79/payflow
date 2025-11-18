# Paquetes Personalizados

Este proyecto incluye dos paquetes personalizados desarrollados específicamente para gestión de pagos y carritos de
compra:

## 📦 Paquetes Disponibles

### 1. Darkraul79/Payflow

**Gestión de Pasarelas de Pago**

Paquete para integración con múltiples pasarelas de pago con soporte actual para Redsys.

#### Características

- ✅ Soporte completo para Redsys (tarjeta y Bizum)
- ✅ Pagos únicos y recurrentes (COF - Credentials On File)
- ✅ Generación y verificación de firmas HMAC-SHA256
- ✅ Manejo de callbacks y notificaciones
- ✅ Conversión automática de importes
- ✅ Mensajes de error localizados
- ✅ Arquitectura extensible para añadir nuevas pasarelas

#### Instalación

El paquete ya está instalado localmente en `packages/payflow`.

#### Uso Básico

**Crear un pago con Redsys:**

```php
use Darkraul79\Payflow\Facades\Gateway;

$payment = Gateway::withRedsys()->createPayment(
    amount: 25.50,
    orderId: 'ORDER123',
    options: [
        'url_ok' => route('payment.success'),
        'url_ko' => route('payment.error'),
        'url_notification' => route('payment.callback'),
        'payment_method' => 'tarjeta', // o 'bizum'
    ]
);

// $payment contiene:
// - Ds_MerchantParameters
// - Ds_Signature
// - Ds_SignatureVersion
// - form_url (URL del TPV)
// - raw_parameters (parámetros originales)
```

**Pagos recurrentes:**

```php
// Primer pago (alta COF)
$payment = Gateway::withRedsys()->createPayment(
    amount: 9.99,
    orderId: 'DONATION001',
    options: [
        'url_ok' => route('donation.response'),
        'url_ko' => route('donation.response'),
        'recurring' => [
            'cof_ini' => 'S',
            'cof_type' => 'R',
        ],
    ]
);

// Cobros posteriores (con identifier)
$payment = Gateway::withRedsys()->createPayment(
    amount: 9.99,
    orderId: 'DONATION001_2',
    options: [
        'recurring' => [
            'identifier' => $savedIdentifier,
            'direct_payment' => 'true',
            'excep_sca' => 'MIT',
        ],
    ]
);
```

**Procesar callback:**

```php
$result = Gateway::withRedsys()->processCallback($request->all());

if ($result['is_valid']) {
    $decodedData = $result['decoded_data'];
    
    if (Gateway::withRedsys()->isSuccessful($request->all())) {
        // Pago exitoso
        $amount = convert_amount_from_redsys($decodedData['Ds_Amount']);
        // Procesar pedido...
    } else {
        // Pago fallido
        $error = Gateway::withRedsys()->getErrorMessage($request->all());
        // Manejar error...
    }
}
```

#### Helpers Disponibles

```php
// Convertir importe a formato Redsys (céntimos)
convert_amount_to_redsys(10.50); // "1050"

// Convertir desde formato Redsys
convert_amount_from_redsys("1050"); // 10.50
```

#### Configuración

Asegúrate de tener configurado en tu `.env`:

```env
REDSYS_KEY=your_secret_key_base64
REDSYS_MERCHANT_CODE=999999999
REDSYS_TERMINAL=001
REDSYS_CURRENCY=978
REDSYS_TRANSACTION_TYPE=0
REDSYS_TRADE_NAME="Tu Comercio"
REDSYS_ENVIRONMENT=test
```

---

### 2. Darkraul79/Cartify

**Gestión de Carritos de Compra**

Paquete simple y eficiente para gestionar carritos de compra en sesión.

#### Características

- ✅ Añadir/eliminar productos
- ✅ Actualizar cantidades
- ✅ Cálculo automático de totales
- ✅ Validación de stock
- ✅ Persistencia en sesión
- ✅ Eventos para tracking

#### Instalación

El paquete ya está instalado localmente en `packages/cartify`.

#### Uso Básico

**Añadir productos al carrito:**

```php
use Darkraul79\Cartify\Facades\Cart;

Cart::add(
    id: $product->id,
    name: $product->name,
    price: $product->price,
    quantity: 1,
    attributes: [
        'image' => $product->image_url,
        'sku' => $product->sku,
    ]
);
```

**Consultar el carrito:**

```php
// Obtener todos los items
$items = Cart::getItems();

// Obtener total
$total = Cart::getTotal();

// Contar items
$count = Cart::count();

// Verificar si está vacío
if (Cart::isEmpty()) {
    // Carrito vacío
}
```

**Actualizar cantidades:**

```php
Cart::update($itemId, [
    'quantity' => 3,
]);
```

**Eliminar items:**

```php
// Eliminar un item
Cart::remove($itemId);

// Vaciar el carrito
Cart::clear();
```

**Validar disponibilidad:**

```php
if (Cart::canCheckout()) {
    // Todos los productos tienen stock
    // Proceder al checkout
}
```

#### Métodos Disponibles

| Método                                  | Descripción                     |
|-----------------------------------------|---------------------------------|
| `add($id, $name, $price, $qty, $attrs)` | Añadir producto al carrito      |
| `update($id, $data)`                    | Actualizar item existente       |
| `remove($id)`                           | Eliminar un item                |
| `clear()`                               | Vaciar el carrito               |
| `getItems()`                            | Obtener todos los items         |
| `getItem($id)`                          | Obtener un item específico      |
| `getTotal()`                            | Calcular total del carrito      |
| `count()`                               | Contar número de items          |
| `isEmpty()`                             | Verificar si está vacío         |
| `canCheckout()`                         | Validar disponibilidad de stock |

---

## 🔄 Migración desde RedsysAPI (Legacy)

Si estás actualizando código antiguo que usaba `RedsysAPI`, aquí está la guía de migración:

### Antes (RedsysAPI - Deprecado)

```php
use App\Helpers\RedsysAPI;

$redsys = new RedsysAPI;
$redsys->setParameter('DS_MERCHANT_AMOUNT', convertNumberToRedSys($amount));
$redsys->setParameter('DS_MERCHANT_ORDER', $order);
// ... más parámetros

$data = [
    'Ds_MerchantParameters' => $redsys->createMerchantParameters(),
    'Ds_Signature' => $redsys->createMerchantSignature(config('redsys.key')),
    // ...
];
```

### Ahora (Payflow - Recomendado)

```php
use Darkraul79\Payflow\Facades\Gateway;

$payment = Gateway::withRedsys()->createPayment(
    amount: $amount,
    orderId: $order,
    options: [
        'url_ok' => route('payment.success'),
        'url_ko' => route('payment.error'),
    ]
);
```

### Funciones Helper

| Legacy (Deprecado)                | Nuevo (Recomendado)                      |
|-----------------------------------|------------------------------------------|
| `convertNumberToRedSys($amount)`  | `convert_amount_to_redsys($amount)`      |
| `convertPriceFromRedsys($amount)` | `convert_amount_from_redsys($amount)`    |
| `RedsysAPI::getRedsysUrl()`       | `Gateway::withRedsys()->getPaymentUrl()` |

---

## 🧪 Testing

Ambos paquetes incluyen tests completos:

```bash
# Tests del paquete Payflow
php artisan test packages/payflow/tests

# Tests del paquete Cartify
php artisan test packages/cartify/tests

# Tests de integración en el proyecto
php artisan test tests/Unit/PaymentProcessGatewayTest.php
```

---

## 📝 Ejemplos de Uso en el Proyecto

### PaymentProcess Service

```php
use App\Services\PaymentProcess;
use App\Models\Order;
use App\Enums\PaymentMethod;

$process = new PaymentProcess(Order::class, [
    'amount' => '25,50',
    'shipping' => 'Envío estándar',
    'shipping_cost' => 5.00,
    'subtotal' => 20.50,
    'payment_method' => PaymentMethod::TARJETA->value,
]);

$redsysData = $process->getFormRedSysData();
// Contiene: Ds_MerchantParameters, Ds_Signature, form_url, etc.
```

### RedsysController (Callbacks)

```php
// En app/Http/Controllers/RedsysController.php
$result = Gateway::withRedsys()->processCallback($request->all());

if (Gateway::withRedsys()->isSuccessful($request->all())) {
    $order->payed($result['decoded_data']);
} else {
    $error = Gateway::withRedsys()->getErrorMessage($request->all());
    $order->error($error, $result['decoded_data']);
}
```

---

## 🚀 Futuras Pasarelas

La arquitectura de Payflow está preparada para añadir más pasarelas:

```php
// Ejemplo futuro:
Gateway::withStripe()->createPayment(...);
Gateway::withPaypal()->createPayment(...);
```

Para implementar una nueva pasarela:

1. Crear clase en `packages/payflow/src/Gateways/`
2. Implementar `GatewayInterface`
3. Registrar en `PaymentServiceProvider`

---

## 📚 Recursos

- **Payflow**: `packages/payflow/README.md`
- **Cartify**: `packages/cartify/README.md`
- **Tests**: `tests/Unit/PaymentProcessGatewayTest.php`
- **Documentación Redsys**: [https://pagosonline.redsys.es/](https://pagosonline.redsys.es/)

---

## 🤝 Contribución

Estos paquetes son parte del proyecto y están en desarrollo activo.

**Versión actual:**

- Payflow: `0.1.0`
- Cartify: `0.1.0`

**Autor**: [@darkraul79](https://github.com/darkraul79)

