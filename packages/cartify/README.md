# Cartify

<p align="center">
  <strong>A flexible and powerful shopping cart package for Laravel</strong>
</p>

<p align="center">
  <a href="https://packagist.org/packages/darkraul79/cartify"><img src="https://img.shields.io/packagist/v/darkraul79/cartify" alt="Latest Version"></a>
  <a href="https://packagist.org/packages/darkraul79/cartify"><img src="https://img.shields.io/packagist/dt/darkraul79/cartify" alt="Total Downloads"></a>
  <a href="https://packagist.org/packages/darkraul79/cartify"><img src="https://img.shields.io/packagist/l/darkraul79/cartify" alt="License"></a>
</p>

> ⚠️ **Alpha Version (0.1.x)** - This package is in early development. APIs may change. Use with caution in production.

---

## Features

- ✅ Simple and intuitive API
- ✅ Automatic price calculations (subtotal, tax, total)
- ✅ Multiple cart instances (cart, wishlist, etc.)
- ✅ Persistent cart for authenticated users
- ✅ Session-based storage
- ✅ Database migrations included
- ✅ Flexible and extensible
- ✅ Laravel 12+ support

---

## Installation

```bash
composer require darkraul79/cartify
```

Publish the configuration and migrations:

```bash
php artisan vendor:publish --provider="Raulsdev\Cartify\CartifyServiceProvider"
```

Run migrations:

```bash
php artisan migrate
```

---

## Configuration

Configure in `.env`:

```env
CARTIFY_TAX_RATE=0.21
CARTIFY_CURRENCY=EUR
CARTIFY_CURRENCY_SYMBOL=€
```

Or publish and edit `config/cartify.php`.

---

## Quick Start

```php
use Raulsdev\Cartify\Facades\Cart;

// Add item to cart
Cart::add(
    id: 1,
    name: 'Product Name',
    quantity: 2,
    price: 29.99,
    options: ['color' => 'red', 'size' => 'M']
);

// Get cart content
$items = Cart::content();

// Calculate totals
$subtotal = Cart::subtotal();
$tax = Cart::tax(0.21); // 21% tax
$total = Cart::total(0.21);

// Update quantity
Cart::update(id: 1, quantity: 3);

// Remove item
Cart::remove(id: 1);

// Clear cart
Cart::clear();

// Count items
$count = Cart::count();
```

---

## Usage

### Multiple Instances

Use different cart instances for cart, wishlist, etc:

```php
// Shopping cart
Cart::instance('cart')->add(1, 'Product A', 1, 29.99);

// Wishlist
Cart::instance('wishlist')->add(2, 'Product B', 1, 49.99);

// Get wishlist content
$wishlist = Cart::instance('wishlist')->content();
```

### User Persistence

Store and restore cart for authenticated users:

```php
// On login
Cart::restore(); // Restore saved cart

// On logout
Cart::store(); // Save cart
```

### Merge Carts

Combine session cart with stored cart:

```php
Cart::merge(); // Merge session cart with stored cart
```

### Search Cart

```php
$redItems = Cart::search(function ($item) {
    return $item['options']['color'] === 'red';
});
```

### Helper Functions

```php
// Get cart instance
$cart = cart();
$wishlist = cart('wishlist');

// Format price
echo format_price(29.99); // "29,99 €"

// Generate order number
$orderNumber = generate_order_number(); // "ORD-202501-A3F9E2"
```

---

## API Reference

### Adding Items

```php
Cart::add(
    id: int|string,
    name: string,
    quantity: int = 1,
    price: float = 0,
    options: array = []
): void
```

### Updating Items

```php
Cart::update(id: int|string, quantity: int): void
```

### Removing Items

```php
Cart::remove(id: int|string): void
```

### Getting Cart Data

```php
Cart::content(): Collection
Cart::get(id: int|string): ?array
Cart::has(id: int|string): bool
Cart::count(): int
Cart::isEmpty(): bool
```

### Calculations

```php
Cart::subtotal(): float
Cart::tax(?float $taxRate = null): float
Cart::total(?float $taxRate = null): float
```

### Cart Management

```php
Cart::clear(): void
Cart::instance(?string $name = null): CartManager
Cart::store(?int $userId = null): void
Cart::restore(?int $userId = null): void
Cart::merge(?int $userId = null): void
```

---

## Example Integration

### Controller

```php
use Raulsdev\Cartify\Facades\Cart;
use App\Models\Product;

class CartController extends Controller
{
    public function add(Request $request)
    {
        $product = Product::findOrFail($request->product_id);
        
        Cart::add(
            id: $product->id,
            name: $product->name,
            quantity: $request->quantity ?? 1,
            price: $product->price
        );
        
        return redirect()->route('cart.index');
    }
    
    public function index()
    {
        return view('cart.index', [
            'items' => Cart::content(),
            'total' => Cart::total(0.21),
        ]);
    }
}
```

### Blade View

```blade
@foreach(Cart::content() as $item)
    <div>
        <h3>{{ $item['name'] }}</h3>
        <p>Price: {{ format_price($item['price']) }}</p>
        <p>Quantity: {{ $item['quantity'] }}</p>
    </div>
@endforeach

<p>Total: {{ format_price(Cart::total(0.21)) }}</p>
```

---

## Database Schema

The package includes a migration for persistent cart storage:

- `cart_items` table with columns:
    - `session_id` - For guest users
    - `user_id` - For authenticated users
    - `product_id` - Product reference
    - `name`, `quantity`, `price`
    - `options` - JSON field for additional data
    - `instance` - Cart instance name

---

## Testing

```bash
composer test
```

---

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

---

## License

MIT License. See [LICENSE](LICENSE) for details.

---

## Credits

- [Raul Sebastian (darkraul79)](https://github.com/darkraul79)

---

## Support

If you find this package helpful, please ⭐ star the repository!

For issues or questions, open an issue on [GitHub](https://github.com/darkraul79/cartify).

