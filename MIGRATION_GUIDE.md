# Guía de Migración a los Paquetes Laravel Commerce y Laravel Gateway

Esta guía te ayudará a migrar tu código actual para usar los nuevos paquetes independientes.

## 📦 Paquetes Creados

### 1. Laravel Commerce (`laravel-commerce/laravel-commerce`)

- Gestión de carrito de compras
- Cálculos de precio, impuestos y totales
- Múltiples instancias de carrito (carrito, wishlist, etc.)
- Almacenamiento persistente para usuarios autenticados

### 2. Laravel Gateway (`laravel-gateway/laravel-gateway`)

- Sistema de pasarelas de pago unificado
- Soporte para Redsys (completo)
- Preparado para Stripe, PayPal, etc.
- Verificación de firmas y callbacks
- Gestión de pagos recurrentes

## 🚀 Instalación

Los paquetes ya están instalados localmente en `packages/`:

```bash
# Ya ejecutado
composer update
php artisan vendor:publish --provider="LaravelGateway\GatewayServiceProvider"
php artisan vendor:publish --provider="LaravelCommerce\CommerceServiceProvider"
```

## 🔧 Configuración

### Variables de Entorno

Actualiza tu `.env` con las nuevas variables:

```env
# Gateway Configuration
PAYMENT_GATEWAY_DEFAULT=redsys

# Redsys (mantén los valores actuales)
REDSYS_KEY=sq7HjrUOBfKmC576ILgskD5srU870gJ7
REDSYS_MERCHANT_CODE=357328590
REDSYS_TERMINAL=1
REDSYS_CURRENCY=978
REDSYS_ENVIRONMENT=test
REDSYS_TRADE_NAME="Fundación Elena Tertre"

# Commerce Configuration
COMMERCE_TAX_RATE=0.21
COMMERCE_CURRENCY=EUR
COMMERCE_CURRENCY_SYMBOL=€
```

## 📝 Ejemplos de Migración

### 1. Migrar uso de Redsys

#### ANTES (usando RedsysAPI):

```php
use App\Helpers\RedsysAPI;

$redSys = new RedsysAPI;
$data = $redSys->getFormDirectPay($pedido);

// Procesar callback
$decodec = json_decode($redSys->decodeMerchantParameters($datos), true);
$firma = $redSys->createMerchantSignatureNotif(config('redsys.key'), $datos);
```

#### DESPUÉS (usando Laravel Gateway):

```php
use LaravelGateway\Facades\Gateway;

// Crear pago
$payment = Gateway::withRedsys()->createPayment(
    amount: $pedido->amount,
    orderId: $pedido->number,
    options: [
        'url_ok' => route('pedido.response'),
        'url_ko' => route('pedido.response'),
        'url_notification' => route('pedido.response'),
    ]
);

// Procesar callback
$result = Gateway::withRedsys()->processCallback($request->all());

if (Gateway::withRedsys()->isSuccessful($request->all())) {
    // Pago exitoso
    $decodedData = $result['decoded_data'];
    $amount = convert_amount_from_redsys($decodedData['Ds_Amount']);
} else {
    $error = Gateway::withRedsys()->getErrorMessage($request->all());
}
```

### 2. Migrar RedsysController

#### ANTES:

```php
class RedsysController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $redSys = new RedsysAPI;
        [$decodec, $firma] = $this->validateRedsysRequest($request, $redSys);
        
        $pedido = Order::where('number', $decodec['Ds_Order'])->firstOrFail();
        
        if ($this->isSuccessfulPayment($redSys, $firma, $decodec)) {
            $pedido->payed($decodec);
        } else {
            $error = $this->getPaymentError($firma, $decodec);
            $pedido->error($error, $decodec);
        }
        
        return redirect()->route('pedido.finalizado', ['pedido' => $pedido->number]);
    }
}
```

#### DESPUÉS:

```php
use LaravelGateway\Facades\Gateway;

class RedsysController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $result = Gateway::withRedsys()->processCallback($request->all());
        $decodedData = $result['decoded_data'];
        
        $pedido = Order::where('number', $decodedData['Ds_Order'])->firstOrFail();
        
        if (Gateway::withRedsys()->isSuccessful($request->all())) {
            $pedido->payed($decodedData);
        } else {
            $error = Gateway::withRedsys()->getErrorMessage($request->all());
            $pedido->error($error, $decodedData);
        }
        
        return redirect()->route('pedido.finalizado', ['pedido' => $pedido->number]);
    }
}
```

### 3. Crear Pago con Bizum

```php
use LaravelGateway\Facades\Gateway;

$payment = Gateway::withRedsys()->createPayment(
    amount: $order->amount,
    orderId: $order->number,
    options: [
        'payment_method' => 'bizum',
        'url_ok' => route('payment.success'),
        'url_ko' => route('payment.error'),
        'url_notification' => route('payment.notification'),
    ]
);
```

### 4. Pagos Recurrentes

```php
// Primer pago (registrar tarjeta)
$payment = Gateway::withRedsys()->createPayment(
    amount: 50.00,
    orderId: 'ORDER-123',
    options: [
        'recurring' => [
            'identifier' => 'REQUIRED',
            'cof_ini' => 'S',
            'cof_type' => 'R',
        ],
        'url_ok' => route('payment.success'),
        'url_ko' => route('payment.error'),
    ]
);

// Pagos posteriores automáticos
$payment = Gateway::withRedsys()->createPayment(
    amount: 50.00,
    orderId: 'ORDER-124',
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

### 5. Usar el Carrito

```php
use LaravelCommerce\Facades\Cart;

// Agregar producto al carrito
Cart::add(
    id: $product->id,
    name: $product->name,
    quantity: 1,
    price: $product->price,
    options: ['color' => 'red', 'size' => 'M']
);

// Obtener contenido del carrito
$items = Cart::content();

// Calcular totales
$subtotal = Cart::subtotal();
$tax = Cart::tax(0.21); // 21% IVA
$total = Cart::total(0.21);

// Actualizar cantidad
Cart::update($productId, 3);

// Eliminar producto
Cart::remove($productId);

// Vaciar carrito
Cart::clear();

// Contar items
$count = Cart::count();
```

### 6. Carrito Persistente para Usuarios

```php
// Al hacer login
public function login(Request $request)
{
    Auth::attempt($credentials);
    
    // Restaurar el carrito guardado del usuario
    Cart::restore();
    
    // O combinar con el carrito actual
    // Cart::merge();
    
    return redirect()->route('home');
}

// Al hacer logout
public function logout(Request $request)
{
    // Guardar carrito antes de cerrar sesión
    Cart::store();
    
    Auth::logout();
    
    return redirect()->route('home');
}
```

### 7. Múltiples Instancias de Carrito

```php
// Carrito de compras
Cart::instance('cart')->add(1, 'Product A', 1, 29.99);

// Lista de deseos
Cart::instance('wishlist')->add(2, 'Product B', 1, 49.99);

// Obtener contenido de la wishlist
$wishlist = Cart::instance('wishlist')->content();
```

## 🔄 Cambios en el Modelo Order

Actualiza el método `payed()` para usar las nuevas funciones:

```php
use function LaravelGateway\Helpers\convert_amount_from_redsys;

public function payed(array $gatewayResponse): void
{
    $this->payments
        ->where('number', $gatewayResponse['Ds_Order'])
        ->firstOrFail()
        ->update([
            'amount' => convert_amount_from_redsys($gatewayResponse['Ds_Amount']),
            'info' => $gatewayResponse,
        ]);

    if (! $this->states()->where('name', OrderStatus::PAGADO->value)->exists()) {
        $this->subtractStocks();
        $this->states()->create([
            'name' => OrderStatus::PAGADO->value,
            'info' => $gatewayResponse,
        ]);
    }

    $this->refresh();
}
```

## 🎨 Helpers Disponibles

### Laravel Gateway

```php
// Obtener gateway
$gateway = gateway('redsys');

// Convertir montos
$redsysAmount = convert_amount_to_redsys(100.50); // "10050"
$amount = convert_amount_from_redsys("10050"); // 100.50
```

### Laravel Commerce

```php
// Obtener carrito
$cart = cart();
$wishlist = cart('wishlist');

// Formatear precio
echo format_price(29.99); // "29,99 €"

// Generar número de pedido
$orderNumber = generate_order_number(); // "ORD-202511-A3F9E2"
```

## 🌐 Agregar Nuevas Pasarelas

Para agregar Stripe u otra pasarela:

```php
// En un ServiceProvider
use LaravelGateway\Facades\Gateway;
use App\Gateways\StripeGateway;

public function boot()
{
    Gateway::extend('stripe', fn () => new StripeGateway());
}

// Usar
$payment = Gateway::withStripe()->createPayment(100.50, 'ORDER-123');
```

## ✅ Ventajas

1. **Código Reutilizable**: Usa los mismos paquetes en diferentes proyectos
2. **API Unificada**: Misma interfaz para todas las pasarelas de pago
3. **Fácil Mantenimiento**: Actualiza el paquete y todos los proyectos se benefician
4. **Testing Simplificado**: Paquetes bien estructurados para testing
5. **Extensible**: Agrega nuevas pasarelas sin modificar el core
6. **Documentado**: README completos con ejemplos

## 📚 Documentación Completa

- [Laravel Gateway README](../packages/laravel-gateway/README.md)
- [Laravel Commerce README](../packages/laravel-commerce/README.md)

## 🚦 Próximos Pasos

1. ✅ Paquetes instalados y configurados
2. ⏳ Migrar controladores actuales
3. ⏳ Actualizar modelos (Order, Payment, Donation)
4. ⏳ Actualizar vistas para usar helpers
5. ⏳ Escribir tests
6. ⏳ Eliminar código legacy (RedsysAPI, etc.)

## 💡 Ejemplo Completo de Flujo de Compra

```php
// 1. Usuario agrega productos al carrito
Cart::add($product->id, $product->name, 1, $product->price);

// 2. Checkout - crear pedido
$order = Order::create([
    'number' => generate_order_number(),
    'amount' => Cart::total(0.21),
    'subtotal' => Cart::subtotal(),
    'taxes' => Cart::tax(0.21),
]);

// 3. Crear items del pedido
foreach (Cart::content() as $item) {
    $order->items()->create([
        'product_id' => $item['id'],
        'quantity' => $item['quantity'],
        'price' => $item['price'],
    ]);
}

// 4. Crear pago con Gateway
$payment = Gateway::withRedsys()->createPayment(
    amount: $order->amount,
    orderId: $order->number,
    options: [
        'url_ok' => route('order.success', $order),
        'url_ko' => route('order.error', $order),
        'url_notification' => route('order.callback'),
    ]
);

// 5. Redirigir a pasarela
return view('payment.form', [
    'action' => $payment['form_url'],
    'params' => $payment,
]);

// 6. Procesar callback (en el controller)
$result = Gateway::withRedsys()->processCallback($request->all());

if (Gateway::withRedsys()->isSuccessful($request->all())) {
    $order->payed($result['decoded_data']);
    Cart::clear();
}
```

## ❓ Preguntas Frecuentes

**P: ¿Puedo usar ambos paquetes en otros proyectos Laravel?**  
R: Sí, son completamente independientes y reutilizables.

**P: ¿Cómo publico estos paquetes en Packagist?**  
R: Crea repositorios separados en GitHub y registra en packagist.org.

**P: ¿Puedo personalizar los paquetes?**  
R: Sí, están en `packages/` y puedes modificarlos libremente.

**P: ¿Qué pasa con mi código actual de Redsys?**  
R: Puedes mantenerlo hasta completar la migración, luego eliminarlo.

