# 🚀 Cómo Usar los Paquetes en Otro Proyecto Laravel

Esta guía te muestra cómo instalar y usar `laravel-commerce` y `laravel-gateway` en un proyecto Laravel nuevo o
existente.

## 📋 Requisitos

- PHP >= 8.3
- Laravel >= 12.0
- Composer

---

## 🔧 Instalación

### Opción 1: Desde Repositorios Locales (Durante Desarrollo)

Si los paquetes están en `packages/` dentro de tu proyecto:

```json
// composer.json
{
    "repositories": [
        {
            "type": "path",
            "url": "./packages/laravel-commerce"
        },
        {
            "type": "path",
            "url": "./packages/laravel-gateway"
        }
    ],
    "require": {
        "laravel-commerce/laravel-commerce": "@dev",
        "laravel-gateway/laravel-gateway": "@dev"
    }
}
```

```bash
composer update
```

### Opción 2: Desde GitHub (Cuando Publiques los Paquetes)

```json
// composer.json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/tu-usuario/laravel-commerce"
        },
        {
            "type": "vcs",
            "url": "https://github.com/tu-usuario/laravel-gateway"
        }
    ],
    "require": {
        "laravel-commerce/laravel-commerce": "^1.0",
        "laravel-gateway/laravel-gateway": "^1.0"
    }
}
```

```bash
composer install
```

### Opción 3: Desde Packagist (Cuando Registres en Packagist)

```bash
composer require laravel-commerce/laravel-commerce
composer require laravel-gateway/laravel-gateway
```

---

## ⚙️ Configuración

### 1. Publicar Archivos de Configuración

```bash
php artisan vendor:publish --provider="LaravelCommerce\CommerceServiceProvider"
php artisan vendor:publish --provider="LaravelGateway\GatewayServiceProvider"
```

Esto creará:

- `config/commerce.php`
- `config/gateway.php`

### 2. Configurar Variables de Entorno

Agrega a tu `.env`:

```env
# Commerce
COMMERCE_TAX_RATE=0.21
COMMERCE_CURRENCY=EUR
COMMERCE_CURRENCY_SYMBOL=€

# Gateway - Default
PAYMENT_GATEWAY_DEFAULT=redsys

# Redsys
REDSYS_KEY=tu-clave-secreta
REDSYS_MERCHANT_CODE=tu-codigo-comercio
REDSYS_TERMINAL=1
REDSYS_CURRENCY=978
REDSYS_ENVIRONMENT=test
REDSYS_TRADE_NAME="Tu Tienda"

# Stripe (opcional)
STRIPE_API_KEY=tu-stripe-key
STRIPE_WEBHOOK_SECRET=tu-webhook-secret

# PayPal (opcional)
PAYPAL_CLIENT_ID=tu-client-id
PAYPAL_CLIENT_SECRET=tu-client-secret
PAYPAL_MODE=sandbox
```

---

## 📦 Uso Básico

### Laravel Commerce (Carrito)

```php
<?php

namespace App\Http\Controllers;

use LaravelCommerce\Facades\Cart;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Agregar producto al carrito
     */
    public function add(Request $request)
    {
        $product = Product::findOrFail($request->product_id);
        
        Cart::add(
            id: $product->id,
            name: $product->name,
            quantity: $request->quantity ?? 1,
            price: $product->price,
            options: [
                'image' => $product->image,
                'sku' => $product->sku,
            ]
        );
        
        return redirect()->route('cart.index')
            ->with('success', 'Producto agregado al carrito');
    }
    
    /**
     * Ver carrito
     */
    public function index()
    {
        $items = Cart::content();
        $subtotal = Cart::subtotal();
        $tax = Cart::tax();
        $total = Cart::total();
        
        return view('cart.index', compact('items', 'subtotal', 'tax', 'total'));
    }
    
    /**
     * Actualizar cantidad
     */
    public function update(Request $request, $id)
    {
        Cart::update($id, $request->quantity);
        
        return back()->with('success', 'Carrito actualizado');
    }
    
    /**
     * Eliminar producto
     */
    public function remove($id)
    {
        Cart::remove($id);
        
        return back()->with('success', 'Producto eliminado');
    }
    
    /**
     * Vaciar carrito
     */
    public function clear()
    {
        Cart::clear();
        
        return redirect()->route('cart.index')
            ->with('success', 'Carrito vaciado');
    }
}
```

### Laravel Gateway (Pagos con Redsys)

```php
<?php

namespace App\Http\Controllers;

use LaravelGateway\Facades\Gateway;
use LaravelCommerce\Facades\Cart;
use App\Models\Order;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    /**
     * Procesar checkout y crear pago
     */
    public function process(Request $request)
    {
        // Validar request...
        
        // Crear pedido
        $order = Order::create([
            'user_id' => auth()->id(),
            'number' => generate_order_number(),
            'subtotal' => Cart::subtotal(),
            'tax' => Cart::tax(),
            'total' => Cart::total(),
            'status' => 'pending',
        ]);
        
        // Crear items del pedido
        foreach (Cart::content() as $item) {
            $order->items()->create([
                'product_id' => $item['id'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
            ]);
        }
        
        // Crear pago con Redsys
        $payment = Gateway::withRedsys()->createPayment(
            amount: $order->total,
            orderId: $order->number,
            options: [
                'url_ok' => route('checkout.success', $order),
                'url_ko' => route('checkout.error', $order),
                'url_notification' => route('checkout.callback'),
            ]
        );
        
        // Mostrar formulario de pago
        return view('checkout.payment', [
            'order' => $order,
            'payment' => $payment,
        ]);
    }
    
    /**
     * Procesar callback de Redsys
     */
    public function callback(Request $request)
    {
        // Procesar respuesta de Redsys
        $result = Gateway::withRedsys()->processCallback($request->all());
        $data = $result['decoded_data'];
        
        // Buscar pedido
        $order = Order::where('number', $data['Ds_Order'])->firstOrFail();
        
        // Verificar si el pago fue exitoso
        if (Gateway::withRedsys()->isSuccessful($request->all())) {
            // Pago exitoso
            $order->update([
                'status' => 'paid',
                'paid_at' => now(),
                'payment_data' => $data,
            ]);
            
            // Vaciar carrito
            Cart::clear();
            
            // Enviar email, etc...
            
            return redirect()->route('checkout.success', $order);
        } else {
            // Pago fallido
            $error = Gateway::withRedsys()->getErrorMessage($request->all());
            
            $order->update([
                'status' => 'failed',
                'payment_error' => $error,
                'payment_data' => $data,
            ]);
            
            return redirect()->route('checkout.error', $order)
                ->with('error', $error);
        }
    }
    
    /**
     * Página de éxito
     */
    public function success(Order $order)
    {
        return view('checkout.success', compact('order'));
    }
    
    /**
     * Página de error
     */
    public function error(Order $order)
    {
        return view('checkout.error', compact('order'));
    }
}
```

---

## 🎨 Vistas de Ejemplo

### Carrito (`resources/views/cart/index.blade.php`)

```blade
<x-layout>
    <h1>Carrito de Compras</h1>
    
    @if(Cart::isEmpty())
        <p>Tu carrito está vacío</p>
        <a href="{{ route('products.index') }}">Ver productos</a>
    @else
        <table>
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Precio</th>
                    <th>Cantidad</th>
                    <th>Subtotal</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach(Cart::content() as $id => $item)
                    <tr>
                        <td>{{ $item['name'] }}</td>
                        <td>{{ format_price($item['price']) }}</td>
                        <td>
                            <form action="{{ route('cart.update', $id) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <input type="number" name="quantity" value="{{ $item['quantity'] }}" min="1">
                                <button type="submit">Actualizar</button>
                            </form>
                        </td>
                        <td>{{ format_price($item['price'] * $item['quantity']) }}</td>
                        <td>
                            <form action="{{ route('cart.remove', $id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        
        <div>
            <p>Subtotal: {{ format_price(Cart::subtotal()) }}</p>
            <p>IVA (21%): {{ format_price(Cart::tax()) }}</p>
            <p><strong>Total: {{ format_price(Cart::total()) }}</strong></p>
        </div>
        
        <div>
            <a href="{{ route('checkout.index') }}">Proceder al pago</a>
            <form action="{{ route('cart.clear') }}" method="POST" style="display: inline;">
                @csrf
                @method('DELETE')
                <button type="submit">Vaciar carrito</button>
            </form>
        </div>
    @endif
</x-layout>
```

### Formulario de Pago (`resources/views/checkout/payment.blade.php`)

```blade
<x-layout>
    <h1>Pagar Pedido #{{ $order->number }}</h1>
    
    <div>
        <p>Total a pagar: {{ format_price($order->total) }}</p>
    </div>
    
    <form action="{{ $payment['form_url'] }}" method="POST" id="payment-form">
        <input type="hidden" name="Ds_SignatureVersion" value="{{ $payment['Ds_SignatureVersion'] }}">
        <input type="hidden" name="Ds_MerchantParameters" value="{{ $payment['Ds_MerchantParameters'] }}">
        <input type="hidden" name="Ds_Signature" value="{{ $payment['Ds_Signature'] }}">
        
        <button type="submit">Pagar con Tarjeta</button>
    </form>
</x-layout>
```

---

## 🔒 Rutas

```php
// routes/web.php

use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;

// Carrito
Route::prefix('cart')->name('cart.')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('index');
    Route::post('/add', [CartController::class, 'add'])->name('add');
    Route::patch('/{id}', [CartController::class, 'update'])->name('update');
    Route::delete('/{id}', [CartController::class, 'remove'])->name('remove');
    Route::delete('/', [CartController::class, 'clear'])->name('clear');
});

// Checkout
Route::prefix('checkout')->name('checkout.')->group(function () {
    Route::get('/', [CheckoutController::class, 'index'])->name('index');
    Route::post('/process', [CheckoutController::class, 'process'])->name('process');
    Route::post('/callback', [CheckoutController::class, 'callback'])->name('callback');
    Route::get('/success/{order}', [CheckoutController::class, 'success'])->name('success');
    Route::get('/error/{order}', [CheckoutController::class, 'error'])->name('error');
});
```

---

## 🧪 Testing

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use LaravelCommerce\Facades\Cart;
use App\Models\Product;

class CartTest extends TestCase
{
    public function test_can_add_product_to_cart(): void
    {
        $product = Product::factory()->create([
            'name' => 'Test Product',
            'price' => 29.99,
        ]);
        
        Cart::add($product->id, $product->name, 1, $product->price);
        
        $this->assertEquals(1, Cart::count());
        $this->assertEquals(29.99, Cart::total());
    }
    
    public function test_can_update_cart_quantity(): void
    {
        Cart::add(1, 'Product', 2, 29.99);
        Cart::update(1, 5);
        
        $this->assertEquals(5, Cart::content()->get(1)['quantity']);
    }
    
    public function test_can_remove_from_cart(): void
    {
        Cart::add(1, 'Product', 1, 29.99);
        Cart::remove(1);
        
        $this->assertTrue(Cart::isEmpty());
    }
}
```

---

## 💡 Tips y Mejores Prácticas

### 1. Persistencia del Carrito

```php
// Al hacer login
public function login(Request $request)
{
    Auth::attempt($credentials);
    Cart::restore(); // Restaurar carrito guardado
    
    return redirect()->intended();
}

// Al hacer logout
public function logout()
{
    Cart::store(); // Guardar carrito
    Auth::logout();
    
    return redirect('/');
}
```

### 2. Múltiples Instancias

```php
// Carrito de compras
Cart::instance('cart')->add(1, 'Product', 1, 29.99);

// Lista de deseos
Cart::instance('wishlist')->add(2, 'Product', 1, 49.99);

// Comparación de productos
Cart::instance('compare')->add(3, 'Product', 1, 39.99);
```

### 3. Agregar Métodos al Modelo Product

```php
// app/Models/Product.php

public function addToCart(int $quantity = 1): void
{
    Cart::add(
        id: $this->id,
        name: $this->name,
        quantity: $quantity,
        price: $this->price,
        options: [
            'image' => $this->image_url,
            'sku' => $this->sku,
        ]
    );
}

// Uso
$product->addToCart(2);
```

### 4. Eventos Personalizados

```php
// En un Service Provider
use Illuminate\Support\Facades\Event;

Event::listen('cart.updated', function ($cart) {
    // Actualizar alguna métrica, etc.
});

// Despachar evento después de agregar al carrito
Cart::add(...);
event('cart.updated', Cart::content());
```

---

## 🚀 Deployment

### Preparación

```bash
# Limpiar caches
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimizar
composer install --no-dev --optimize-autoloader
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Variables de Entorno en Producción

Asegúrate de configurar en tu servidor:

```env
REDSYS_ENVIRONMENT=production
REDSYS_KEY=tu-clave-real
REDSYS_MERCHANT_CODE=tu-codigo-real
```

---

## ❓ Solución de Problemas

### Problema: Paquetes no se encuentran

```bash
composer dump-autoload
php artisan optimize:clear
```

### Problema: Configuración no se actualiza

```bash
php artisan config:clear
php artisan config:cache
```

### Problema: Carrito no persiste

Verifica que las sesiones estén configuradas correctamente en `config/session.php`.

---

## 📚 Recursos

- [Laravel Commerce README](https://github.com/tu-usuario/laravel-commerce)
- [Laravel Gateway README](https://github.com/tu-usuario/laravel-gateway)
- [Documentación de Redsys](https://pagosonline.redsys.es)

---

## 🎉 ¡Listo!

Ahora tienes un carrito de compras completo y un sistema de pagos flexible en tu proyecto Laravel. 🚀

