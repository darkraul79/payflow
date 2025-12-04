# SnapshotHelper

## Introducción

`SnapshotHelper` es una clase utilitaria diseñada para capturar snapshots (instantáneas) de datos de modelos en el
momento del dispatch de jobs y mailables.

## Problema que Resuelve

Cuando encolas jobs o mailables que dependen de datos de modelos (como `Order` o `Donation`), Laravel serializa toda la
instancia del modelo. Si el modelo cambia antes de que la cola procese el trabajo, la serialización puede incluir
relaciones cargadas con datos desactualizados.

### Ejemplo del Problema

```php
// En el dispatch
$order = Order::find(1); // status = PENDIENTE
UpdateOrderStateEvent::dispatch($order);

// Mientras el mailable está en cola...
$order->update(['status' => 'PAGADO']);

// Cuando la cola procesa el mailable
$mailable->getSubject(); // Usa 'PAGADO' ¡Incorrecto!
```

## Solución

En lugar de serializar el modelo completo, capturamos un snapshot de los datos primitivos necesarios:

```php
// En el dispatcher
$order = Order::find(1); // status = PENDIENTE
$snapshot = SnapshotHelper::fromOrder($order);
// $snapshot = ['id' => 1, 'stateName' => 'PENDIENTE', ...]

// Se serializa el snapshot (datos primitivos), no el modelo
// Cuando la cola lo deserializa, tenemos los datos del momento del dispatch
```

## API

### `fromOrder(Order $order): array`

Captura snapshot de un Order con su estado actual.

**Retorna:**

```php
[
    'id' => int,
    'number' => string,
    'stateName' => string|null,
    'stateInfo' => array|null,
]
```

**Ejemplo:**

```php
public function __construct(Order $order, ?string $stateName = null)
{
    $orderSnapshot = SnapshotHelper::fromOrder($order);
    $this->orderId = $orderSnapshot['id'];
    $this->stateName = $stateName ?? $orderSnapshot['stateName'];
}
```

---

### `fromDonation(Donation $donation): array`

Captura snapshot de una Donation con su estado actual.

**Retorna:**

```php
[
    'id' => int,
    'type' => string,
    'stateName' => string|null,
    'identifier' => string|null,
    'nextPayment' => string|null,
]
```

**Ejemplo:**

```php
public function __construct(Donation $donation)
{
    $snapshot = SnapshotHelper::fromDonation($donation);
    
    $this->donationId = $snapshot['id'];
    $this->donationType = $snapshot['type'];
    $this->identifier = $snapshot['identifier'];
}
```

---

### `orderUserSnapshot(Order $order): array`

Captura snapshot de datos del usuario desde un Order.

**Retorna:**

```php
[
    'id' => int,
    'name' => string,
]
```

**Ejemplo:**

```php
public function __construct(Order $order)
{
    $userSnapshot = SnapshotHelper::orderUserSnapshot($order);
    $this->orderId = $userSnapshot['id'];
    $this->userName = $userSnapshot['name'];
}
```

---

### `donationDataSnapshot(Donation $donation): array`

Captura snapshot de datos del certificado y donación.

**Retorna:**

```php
[
    'id' => int,
    'name' => string,
    'amount' => string,  // formateado
    'frequency' => string,
    'payed' => bool,
]
```

**Ejemplo:**

```php
public function __construct(Donation $donation)
{
    $snapshot = SnapshotHelper::donationDataSnapshot($donation);
    
    $this->certificateName = $snapshot['name'];
    $this->formattedAmount = $snapshot['amount'];
    $this->payed = $snapshot['payed'];
}
```

## Patrones de Uso

### En Mailables Encolados

```php
class MyMailable extends Mailable implements ShouldQueue
{
    private string $recipientName;
    private string $status;

    public function __construct(Order $order, ?string $statusSnapshot = null)
    {
        // Capturar snapshot al crear el mailable
        $userSnapshot = SnapshotHelper::orderUserSnapshot($order);
        $this->recipientName = $userSnapshot['name'];
        
        // Usar snapshot o parámetro explícito
        $this->status = $statusSnapshot ?? 'default';
    }

    public function content(): Content
    {
        // Usar siempre la snapshot, no el modelo
        return new Content(
            markdown: 'emails.my-mail',
            with: ['name' => $this->recipientName],
        );
    }
}
```

### En Jobs Encolados

```php
class MyJob implements ShouldQueue
{
    public int $donationId;
    public string $donationType;

    public function __construct(Donation $donation)
    {
        // Capturar snapshot al crear el job
        $snapshot = SnapshotHelper::fromDonation($donation);
        
        $this->donationId = $snapshot['id'];
        $this->donationType = $snapshot['type'];
    }

    public function handle(): void
    {
        // Usar snapshot para validaciones
        if ($this->donationType !== 'RECURRENTE') {
            return; // El tipo no cambió desde el dispatch
        }

        // Si necesitamos datos frescos, recargamos el modelo
        $donation = Donation::find($this->donationId);
        // ...proceso...
    }
}
```

## Consideraciones de Diseño

### ✅ Ventajas del Snapshot

1. **Consistencia**: Los datos en cola reflejan siempre el momento del dispatch
2. **Serialización ligera**: Solo datos primitivos, no relaciones completas
3. **Seguridad**: No expone el modelo completo en la cola
4. **Debugging**: Los logs muestran exactamente qué datos se usaron

### ⚠️ Cuándo No Usar

- Si necesitas todos los datos del modelo (usa recargar con `find()`)
- Si los datos son muy grandes (el snapshot debe ser mínimo)
- Si el modelo cambia muy frecuentemente (mejor un table lock)

## Extensibilidad

Para otros modelos con estados mutables, crea métodos similares en `SnapshotHelper`:

```php
public static function fromMyModel(MyModel $model): array
{
    $lastState = $model->states()->orderBy('id', 'desc')->first();

    return [
        'id' => $model->id,
        'stateName' => $lastState?->name,
        'otherData' => $model->otherData,
    ];
}
```

## Referencias

- `app/Support/SnapshotHelper.php` - Implementación
- `app/Mail/OrderStateUpdate.php` - Ejemplo: Mailable
- `app/Mail/DonationNewMail.php` - Ejemplo: Mailable
- `app/Jobs/ProcessDonationPaymentJob.php` - Ejemplo: Job

## Véase También

- [Patrón de Snapshot - Documentación Completa](../SNAPSHOT_PATTERN_IMPLEMENTATION.md)
- [Laravel Queued Mails](https://laravel.com/docs/mail#queued-mails)
- [Queued Jobs](https://laravel.com/docs/queues#creating-jobs)

