# 📦 Paquetes Laravel - Commerce & Gateway

Este proyecto ahora incluye dos paquetes independientes y reutilizables para Laravel:

## 🛒 Laravel Commerce

**Gestión de Carrito de Compras**

Sistema completo de carrito con cálculos automáticos, múltiples instancias, y persistencia para usuarios autenticados.

📁 **Ubicación:** `packages/laravel-commerce/`  
📖 **Documentación:** [packages/laravel-commerce/README.md](packages/laravel-commerce/README.md)

```php
use LaravelCommerce\Facades\Cart;

Cart::add(1, 'Producto', 1, 29.99);
$total = Cart::total(0.21); // Con IVA 21%
```

---

## 💳 Laravel Gateway

**Sistema Multi-Pasarela de Pagos**

Interfaz unificada para múltiples pasarelas de pago (Redsys, Stripe, PayPal, etc.)

📁 **Ubicación:** `packages/laravel-gateway/`  
📖 **Documentación:** [packages/laravel-gateway/README.md](packages/laravel-gateway/README.md)

```php
use LaravelGateway\Facades\Gateway;

// Redsys
$payment = Gateway::withRedsys()->createPayment(100.50, 'ORDER-123', [
    'url_ok' => route('payment.success'),
]);

// Stripe (preparado)
$payment = Gateway::withStripe()->createPayment(100.50, 'ORDER-123');
```

---

## 📚 Documentación Completa

### Guías Principales

- 📖 **[REFACTORING_SUMMARY.md](REFACTORING_SUMMARY.md)** - Resumen completo de lo creado
- 📖 **[MIGRATION_GUIDE.md](MIGRATION_GUIDE.md)** - Cómo migrar tu código actual
- 📖 **[IMPLEMENTATION_CHECKLIST.md](IMPLEMENTATION_CHECKLIST.md)** - Lista de tareas para implementar
- 📖 **[HOW_TO_USE_IN_OTHER_PROJECTS.md](HOW_TO_USE_IN_OTHER_PROJECTS.md)** - Usar en otros proyectos

### READMEs de Paquetes

- 📄 [Laravel Commerce README](packages/laravel-commerce/README.md)
- 📄 [Laravel Gateway README](packages/laravel-gateway/README.md)

### Ejemplos de Código

- 📝 [RedsysControllerRefactored.php](app/Http/Controllers/RedsysControllerRefactored.php)
- 📝 [CartControllerRefactored.php](app/Http/Controllers/CartControllerRefactored.php)

---

## 🚀 Quick Start

### 1. Los paquetes ya están instalados

```bash
✅ Instalados via Composer (symlinked desde packages/)
✅ Configuraciones publicadas en config/
✅ Service Providers registrados
✅ Facades disponibles
```

### 2. Configurar variables de entorno

Actualiza tu `.env`:

```env
# Gateway
PAYMENT_GATEWAY_DEFAULT=redsys

# Redsys
REDSYS_KEY=tu-clave
REDSYS_MERCHANT_CODE=tu-codigo
REDSYS_TERMINAL=1
REDSYS_ENVIRONMENT=test

# Commerce
COMMERCE_TAX_RATE=0.21
COMMERCE_CURRENCY=EUR
COMMERCE_CURRENCY_SYMBOL=€
```

### 3. Usar en tu código

```php
// Carrito
use LaravelCommerce\Facades\Cart;

Cart::add($product->id, $product->name, 1, $product->price);
$items = Cart::content();
$total = Cart::total(0.21);

// Pagos
use LaravelGateway\Facades\Gateway;

$payment = Gateway::withRedsys()->createPayment(
    amount: $order->total,
    orderId: $order->number,
    options: [
        'url_ok' => route('order.success'),
        'url_ko' => route('order.error'),
    ]
);
```

---

## ✨ Características Principales

### Laravel Commerce

- ✅ Agregar/actualizar/eliminar productos
- ✅ Cálculos automáticos (subtotal, IVA, total)
- ✅ Múltiples instancias (carrito, wishlist, etc.)
- ✅ Persistencia para usuarios autenticados
- ✅ Búsqueda de items
- ✅ Helpers útiles

### Laravel Gateway

- ✅ **Redsys completamente implementado**
- ✅ Soporte para Bizum
- ✅ Pagos recurrentes
- ✅ Verificación automática de firmas
- ✅ Gestión de callbacks
- ✅ Preparado para Stripe, PayPal, etc.
- ✅ API unificada para todas las pasarelas

---

## 🎯 Ventajas

### ♻️ Reutilizable

Usa los mismos paquetes en múltiples proyectos Laravel

### 🔌 Extensible

Agrega nuevas pasarelas de pago fácilmente

### 🧪 Testeable

Arquitectura limpia y fácil de testear

### 📖 Documentado

Documentación completa con ejemplos

### 🎨 API Limpia

Código moderno y fácil de usar

---

## 📊 Estado del Proyecto

```
✅ Fase 1: Creación de Paquetes - COMPLETADO
   ├── ✅ Laravel Commerce creado
   ├── ✅ Laravel Gateway creado
   ├── ✅ Redsys completamente implementado
   ├── ✅ Instalado y configurado
   ├── ✅ Documentación completa
   └── ✅ Ejemplos de uso

⏳ Fase 2: Testing y Validación - PENDIENTE
   ├── ⏳ Probar paquetes en tinker
   ├── ⏳ Verificar funcionalidades
   └── ⏳ Escribir tests

⏳ Fase 3: Migración - PENDIENTE
   ├── ⏳ Migrar RedsysController
   ├── ⏳ Migrar CartController
   ├── ⏳ Actualizar modelos
   └── ⏳ Actualizar vistas

⏳ Fase 4: Limpieza - PENDIENTE
   ├── ⏳ Eliminar RedsysAPI.php
   ├── ⏳ Eliminar config/redsys.php
   └── ⏳ Limpiar código legacy
```

---

## 🧪 Probar Rápidamente

```bash
php artisan tinker
```

```php
// Probar Gateway
use LaravelGateway\Facades\Gateway;
$payment = Gateway::withRedsys()->createPayment(100.50, 'TEST-123', [
    'url_ok' => 'https://example.com/ok',
]);
dump($payment);

// Probar Cart
use LaravelCommerce\Facades\Cart;
Cart::add(1, 'Test Product', 2, 29.99);
dump(Cart::content());
dump(Cart::total(0.21));
```

---

## 📞 Soporte

### Documentación

- 📖 Lee las guías en la raíz del proyecto
- 📄 Revisa los READMEs de cada paquete
- 📝 Consulta los ejemplos de código

### Recursos Útiles

- [Documentación de Redsys](https://pagosonline.redsys.es)
- [Documentación de Laravel](https://laravel.com/docs)

---

## 🔜 Próximos Pasos

1. **Probar los paquetes** - Usa tinker para verificar funcionalidad
2. **Revisar documentación** - Lee las guías de migración
3. **Migrar un controlador** - Empieza con uno como prueba
4. **Testear todo** - Asegúrate de que funciona correctamente
5. **Desplegar** - Cuando estés seguro, despliega a producción

---

## 💡 Ejemplos Rápidos

### Flujo Completo de Compra

```php
// 1. Agregar al carrito
Cart::add($product->id, $product->name, 1, $product->price);

// 2. Calcular total
$total = Cart::total(0.21);

// 3. Crear pedido
$order = Order::create([
    'number' => generate_order_number(),
    'total' => $total,
]);

// 4. Crear pago
$payment = Gateway::withRedsys()->createPayment(
    amount: $order->total,
    orderId: $order->number,
    options: ['url_ok' => route('order.success', $order)]
);

// 5. Mostrar formulario de pago
return view('payment.form', ['payment' => $payment]);
```

### Procesar Callback de Redsys

```php
public function callback(Request $request)
{
    $result = Gateway::withRedsys()->processCallback($request->all());
    
    if (Gateway::withRedsys()->isSuccessful($request->all())) {
        $order->markAsPaid();
        Cart::clear();
        return redirect()->route('order.success');
    }
    
    $error = Gateway::withRedsys()->getErrorMessage($request->all());
    return redirect()->route('order.error')->with('error', $error);
}
```

---

## 🎉 ¡Todo Listo!

Los paquetes están **instalados, configurados y listos para usar**.

**Empieza leyendo:** [REFACTORING_SUMMARY.md](REFACTORING_SUMMARY.md)

---

## 📝 Licencia

MIT

---

**Creado con ❤️ para proyectos Laravel**

