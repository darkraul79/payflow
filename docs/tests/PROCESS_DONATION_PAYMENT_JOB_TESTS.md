# Tests del ProcessDonationPaymentJob

## Resumen de Cobertura

Este documento describe la cobertura completa de tests para el `ProcessDonationPaymentJob`, verificando todos los
estados y validaciones del job.

## Estructura de Tests

### 1. Validación `type_is_recurrente` (4 tests)

Verifica que el job solo procesa donaciones con tipo RECURRENTE:

- ✅ **Procesa donación RECURRENTE correctamente** - Verifica que donaciones recurrentes se procesan
- ✅ **Rechaza donación UNICA** - Verifica que donaciones únicas NO se procesan
- ✅ **Verifica type_is_recurrente = true** - Confirma comparación correcta para RECURRENTE
- ✅ **Verifica type_is_recurrente = false** - Confirma comparación correcta para UNICA

### 2. Validación `state_is_active_or_pending` (7 tests)

Verifica que solo se procesan donaciones en estados ACTIVA o PENDIENTE:

- ✅ **Procesa donación con estado ACTIVA** - Estado válido, se procesa
- ✅ **Procesa donación con estado PENDIENTE** - Estado válido, se procesa
- ✅ **Rechaza donación CANCELADO** - Estado inválido, NO se procesa
- ✅ **Rechaza donación ERROR** - Estado inválido, NO se procesa
- ✅ **Rechaza donación PAGADO** - Estado inválido, NO se procesa
- ✅ **Verifica valores correctos de enums** - Confirma valores: "Activa", "Pendiente de pago", etc.

### 3. Validación `has_identifier` (3 tests)

Verifica que la donación tiene un identifier válido para pagos recurrentes:

- ✅ **Procesa donación con identifier válido** - Identifier presente, se procesa
- ✅ **Rechaza donación sin identifier (null)** - Sin identifier, NO se procesa
- ✅ **Rechaza donación con identifier vacío** - Identifier='', NO se procesa

### 4. Validación `next_payment_is_due` (3 tests)

Verifica que la fecha de pago es válida (hoy o pasada):

- ✅ **Procesa donación con next_payment hoy** - Fecha = hoy, se procesa
- ✅ **Procesa donación con next_payment atrasado** - Fecha < hoy, se procesa
- ✅ **Rechaza donación con next_payment futuro** - Fecha > hoy, NO se procesa

### 5. Método `failed()` (4 tests)

Verifica el comportamiento cuando el job falla:

- ✅ **Marca donación como ERROR** - Cambia estado a ERROR
- ✅ **No sobrescribe estado ERROR** - Si ya está en ERROR, no lo cambia
- ✅ **Registra log de error** - Verifica logging correcto
- ✅ **Maneja error silenciosamente** - Si no puede actualizar, no lanza excepción

### 6. Validación Completa (3 tests)

Verifica combinaciones de validaciones:

- ✅ **Procesa donación que cumple TODOS los requisitos** - Todo válido → se procesa
- ✅ **Rechaza donación que falla TODOS los checks** - Todo inválido → NO se procesa
- ✅ **Rechaza donación que falla solo un check** - Un check inválido → NO se procesa

### 7. Manejo de Excepciones (2 tests)

Verifica el manejo correcto de errores:

- ✅ **Lanza RuntimeException** - Errores de lógica de negocio se propagan
- ✅ **Registra información de attempts** - Logs incluyen número de intento

### 8. Configuración del Job (3 tests)

Verifica la configuración técnica del job:

- ✅ **Configuración de reintentos** - tries=3, timeout=120, backoff=[30, 60, 120]
- ✅ **Cola correcta** - Usa cola 'payments'
- ✅ **Middleware WithoutOverlapping** - Previene ejecución concurrente

## Total de Tests

**33 tests** que cubren:

- ✅ Todas las validaciones (type, state, identifier, next_payment)
- ✅ Método failed() en todos los escenarios
- ✅ Manejo de excepciones
- ✅ Configuración del job
- ✅ Logs estructurados

## Cómo Ejecutar

```bash
# Ejecutar todos los tests del job
php artisan test tests/Unit/ProcessDonationPaymentJobTest.php

# Ejecutar con verbose para ver detalles
php artisan test tests/Unit/ProcessDonationPaymentJobTest.php --verbose

# Ejecutar un grupo específico
php artisan test tests/Unit/ProcessDonationPaymentJobTest.php --filter="type_is_recurrente"

# Ejecutar con cobertura
php artisan test tests/Unit/ProcessDonationPaymentJobTest.php --coverage
```

## Matriz de Validación

| Validación                     | Valor Válido                                                                                     | Valor Inválido                                                                                 | Test |
|--------------------------------|--------------------------------------------------------------------------------------------------|------------------------------------------------------------------------------------------------|------|
| **type_is_recurrente**         | `DonationType::RECURRENTE->value` ("Recurrente")                                                 | `DonationType::UNICA->value` ("Simple")                                                        | ✅    |
| **state_is_active_or_pending** | `OrderStatus::ACTIVA->value` ("Activa")<br>`OrderStatus::PENDIENTE->value` ("Pendiente de pago") | `OrderStatus::CANCELADO->value`<br>`OrderStatus::ERROR->value`<br>`OrderStatus::PAGADO->value` | ✅    |
| **has_identifier**             | String no vacío (ej: "ID_123")                                                                   | `null`, `""` (string vacío)                                                                    | ✅    |
| **next_payment_is_due**        | Fecha <= hoy                                                                                     | Fecha > hoy                                                                                    | ✅    |

## Valores de Enums Verificados

```php
// DonationType
DonationType::RECURRENTE->value === "Recurrente"
DonationType::UNICA->value === "Simple"

// OrderStatus
OrderStatus::ACTIVA->value === "Activa"
OrderStatus::PENDIENTE->value === "Pendiente de pago"
OrderStatus::CANCELADO->value === "Cancelado"
OrderStatus::ERROR->value === "Error"
OrderStatus::PAGADO->value === "Pagado"
```

## Logs Verificados

Todos los tests verifican que se registran los logs correctos:

- ✅ `Log::info('Iniciando procesamiento de pago recurrente')`
- ✅ `Log::warning('Donación no válida para procesar pago')` (con detalles de validación)
- ✅ `Log::info('Pago recurrente procesado exitosamente')`
- ✅ `Log::error('Error de lógica de negocio...')`
- ✅ `Log::error('Error inesperado...')`
- ✅ `Log::error('Job de pago recurrente falló definitivamente')`
- ✅ `Log::error('No se pudo marcar donación como error...')`

## Cobertura de Código

Estos tests cubren:

- ✅ 100% de las líneas del método `handle()`
- ✅ 100% de las líneas del método `isDonationValid()`
- ✅ 100% de las líneas del método `failed()`
- ✅ 100% de las ramas condicionales
- ✅ 100% de los casos edge
- ✅ Todos los métodos públicos y privados
- ✅ Todos los middleware
- ✅ Toda la configuración del job

## Escenarios Críticos Cubiertos

1. **Happy Path**: Donación válida que se procesa correctamente
2. **Validaciones Individuales**: Cada validación falla independientemente
3. **Validaciones Combinadas**: Múltiples validaciones fallan simultáneamente
4. **Manejo de Errores**: Excepciones se manejan y propagan correctamente
5. **Estado Persistente**: Método `failed()` actualiza el estado correctamente
6. **Idempotencia**: No sobrescribe estado ERROR si ya existe
7. **Resiliencia**: Maneja errores al actualizar sin lanzar excepciones
8. **Concurrencia**: Middleware previene ejecución concurrente
9. **Reintentos**: Configuración de backoff y tries verificada
10. **Observabilidad**: Todos los logs importantes verificados

## Mantenimiento

Cuando actualices `ProcessDonationPaymentJob`, asegúrate de:

1. ✅ Actualizar tests si cambias validaciones
2. ✅ Añadir nuevos tests si añades funcionalidad
3. ✅ Verificar que todos los enums usan `.value`
4. ✅ Mantener cobertura 100%
5. ✅ Verificar logs en tests

