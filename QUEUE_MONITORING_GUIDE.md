# 🚀 Guía de Configuración y Monitoreo de Colas (Queue Workers)

## 📋 Índice

1. [Verificación Rápida](#verificación-rápida)
2. [Configuración del Servidor](#configuración-del-servidor)
3. [Monitoreo en Tiempo Real](#monitoreo-en-tiempo-real)
4. [Resolución de Problemas](#resolución-de-problemas)
5. [Supervisor (Producción)](#supervisor-producción)

---

## ✅ Verificación Rápida

### 1. Comando de Verificación Automática

```bash
# Ejecuta el comando personalizado de verificación
php artisan queue:verify
```

Este comando te mostrará:

- ✓ Configuración actual de colas
- ✓ Trabajos pendientes
- ✓ Trabajos fallidos
- ✓ Recomendaciones

### 2. Verificación Manual

#### ¿Está corriendo el queue worker?

```bash
# En el servidor
ps aux | grep "queue:work"

# Si no hay salida, el worker NO está corriendo
```

#### ¿Cuántos trabajos hay en cola?

```bash
# Para driver 'database'
php artisan queue:monitor

# O consulta directamente
php artisan tinker
>>> \DB::table('jobs')->count()
```

#### ¿Hay trabajos fallidos?

```bash
php artisan queue:failed
```

---

## ⚙️ Configuración del Servidor

### 1. **Desarrollo Local**

Para desarrollo, el driver `sync` está bien (procesa inmediatamente):

```bash
# .env
QUEUE_CONNECTION=sync
```

O si quieres probar colas en desarrollo:

```bash
# .env
QUEUE_CONNECTION=database

# Luego ejecuta en una terminal separada:
php artisan queue:work --verbose
```

### 2. **Producción**

Cambia a un driver que soporte colas:

```bash
# .env
QUEUE_CONNECTION=database  # O 'redis', 'sqs', etc.
```

#### Crear tabla de trabajos (si usas database)

```bash
php artisan queue:table
php artisan queue:failed-table
php artisan migrate
```

---

## 📊 Monitoreo en Tiempo Real

### 1. **Ver Jobs Procesándose**

```bash
# Ejecuta el worker con verbose para ver cada job
php artisan queue:work --verbose

# Con más detalles
php artisan queue:work --verbose --tries=3
```

Salida esperada:

```
[2024-01-15 10:30:45] Processing: App\Mail\InvoiceMailable
[2024-01-15 10:30:46] Processed:  App\Mail\InvoiceMailable
```

### 2. **Monitorear la Cola**

```bash
# Ver estadísticas en tiempo real
php artisan queue:monitor

# Ver tamaño de la cola
php artisan queue:work --once  # Procesa 1 job y termina
```

### 3. **Dashboard de Horizon** (opcional, si usas Redis)

```bash
composer require laravel/horizon
php artisan horizon:install
php artisan horizon
```

Luego accede a: `http://tu-app.test/horizon`

---

## 🔧 Resolución de Problemas

### ❌ Los emails no se envían

**Diagnóstico:**

```bash
# 1. Verifica que los emails estén en cola
php artisan tinker
>>> \DB::table('jobs')->count()  // Debería ser > 0

# 2. Verifica si el worker está corriendo
ps aux | grep "queue:work"

# 3. Revisa logs
tail -f storage/logs/laravel.log
```

**Solución:**

```bash
# Inicia el worker
php artisan queue:work --verbose

# O en background
nohup php artisan queue:work > /dev/null 2>&1 &
```

### ❌ Jobs fallan constantemente

```bash
# Ver jobs fallidos con detalles
php artisan queue:failed

# Ver el error de un job específico
php artisan queue:failed

# Reintentar todos los jobs fallidos
php artisan queue:retry all

# Reintentar un job específico
php artisan queue:retry [id]

# Limpiar jobs fallidos antiguos
php artisan queue:flush
```

### ❌ La cola está "atascada"

```bash
# Ver cuántos jobs hay pendientes
php artisan queue:monitor

# Limpiar jobs atascados (¡cuidado en producción!)
php artisan queue:clear

# Reiniciar el worker
php artisan queue:restart
```

### ❌ Worker se detiene después de un rato

**Problema:** El worker se detiene por cambios en el código o errores fatales.

**Solución:** Usa **Supervisor** (ver abajo) para reiniciar automáticamente.

---

## 🏭 Supervisor (Producción) - RECOMENDADO

Supervisor mantiene el queue worker corriendo **siempre**, reiniciándolo automáticamente si falla.

### 1. Instalar Supervisor

```bash
# Ubuntu/Debian
sudo apt-get install supervisor

# CentOS/RHEL
sudo yum install supervisor

# Verificar instalación
sudo supervisorctl status
```

### 2. Configurar Supervisor

Crea el archivo de configuración:

```bash
sudo nano /etc/supervisor/conf.d/fundacion-queue-worker.conf
```

Pega esta configuración (ya creada en `supervisor-queue-worker.conf`):

```ini
[program:fundacion-queue-worker]
process_name = %(program_name)s_%(process_num)02d
command = php /var/www/fundacion/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart = true
autorestart = true
stopasgroup = true
killasgroup = true
user = www-data
numprocs = 2
redirect_stderr = true
stdout_logfile = /var/www/fundacion/storage/logs/queue-worker.log
stopwaitsecs = 3600
```

**⚠️ IMPORTANTE:** Cambia `/var/www/fundacion` a la ruta real de tu proyecto.

### 3. Iniciar Supervisor

```bash
# Recargar configuración
sudo supervisorctl reread
sudo supervisorctl update

# Iniciar el worker
sudo supervisorctl start fundacion-queue-worker:*

# Ver estado
sudo supervisorctl status fundacion-queue-worker:*
```

Salida esperada:

```
fundacion-queue-worker:fundacion-queue-worker_00   RUNNING   pid 12345, uptime 0:05:23
fundacion-queue-worker:fundacion-queue-worker_01   RUNNING   pid 12346, uptime 0:05:23
```

### 4. Comandos Útiles de Supervisor

```bash
# Ver todos los procesos
sudo supervisorctl status

# Reiniciar el worker (después de desplegar código nuevo)
sudo supervisorctl restart fundacion-queue-worker:*

# Detener el worker
sudo supervisorctl stop fundacion-queue-worker:*

# Ver logs en tiempo real
tail -f /var/www/fundacion/storage/logs/queue-worker.log
```

### 5. Desplegar Código Nuevo

**Cada vez que actualices el código, DEBES reiniciar el worker:**

```bash
# En tu script de deploy, agrega:
php artisan queue:restart

# O con supervisor:
sudo supervisorctl restart fundacion-queue-worker:*
```

---

## 📈 Monitoreo en Producción

### 1. Verificar que Supervisor está corriendo

```bash
# Estado de los workers
sudo supervisorctl status fundacion-queue-worker:*

# Si no están corriendo:
sudo supervisorctl start fundacion-queue-worker:*
```

### 2. Alertas Automáticas (opcional)

Crea un cronjob para alertarte si hay muchos jobs fallidos:

```bash
# En crontab -e
*/15 * * * * /usr/bin/php /var/www/fundacion/artisan queue:monitor --max=50 || echo "Queue alert!" | mail -s "Queue Alert" admin@tudominio.com
```

### 3. Logs

```bash
# Logs del worker
tail -f storage/logs/queue-worker.log

# Logs de Laravel
tail -f storage/logs/laravel.log

# Logs de supervisor
sudo tail -f /var/log/supervisor/supervisord.log
```

---

## 🧪 Pruebas

### Verificar que los Emails se Encolan

```bash
php artisan tinker

# Enviar un email de prueba
>>> $order = App\Models\Order::first();
>>> $service = app(App\Services\InvoiceService::class);
>>> $service->generateForOrder($order, sendEmail: true);

# Verificar que se encoló
>>> \DB::table('jobs')->count()  // Debería ser > 0

# Procesar manualmente
>>> exit
php artisan queue:work --once --verbose
```

---

## 📋 Checklist para Producción

- [ ] `QUEUE_CONNECTION=database` (o redis/sqs) en `.env`
- [ ] Tablas `jobs` y `failed_jobs` creadas
- [ ] Supervisor instalado y configurado
- [ ] Workers corriendo: `sudo supervisorctl status`
- [ ] Logs monitoreados: `tail -f storage/logs/queue-worker.log`
- [ ] Script de deploy reinicia workers: `php artisan queue:restart`
- [ ] Cronjob para limpiar jobs fallidos antiguos (opcional)
- [ ] Sistema de alertas configurado (opcional)

---

## 🎯 Comandos Rápidos de Referencia

```bash
# Verificación general
php artisan queue:verify

# Iniciar worker (desarrollo)
php artisan queue:work --verbose

# Monitorear cola
php artisan queue:monitor

# Ver jobs fallidos
php artisan queue:failed

# Reintentar jobs fallidos
php artisan queue:retry all

# Limpiar jobs fallidos
php artisan queue:flush

# Reiniciar workers (después de deploy)
php artisan queue:restart

# Con supervisor
sudo supervisorctl status fundacion-queue-worker:*
sudo supervisorctl restart fundacion-queue-worker:*
```

---

## ✅ Verificación Final

Ejecuta estos comandos para asegurarte de que todo funciona:

```bash
# 1. Verificación automática
php artisan queue:verify

# 2. ¿Está corriendo el worker?
ps aux | grep "queue:work"

# 3. ¿Hay jobs pendientes?
php artisan tinker
>>> \DB::table('jobs')->count()

# 4. Enviar un email de prueba
>>> $order = App\Models\Order::first();
>>> app(App\Services\InvoiceService::class)->generateForOrder($order, sendEmail: true);
>>> exit

# 5. Procesar y verificar
php artisan queue:work --once --verbose
```

**Si ves el output del email procesándose, ¡todo funciona!** ✅

---

## 🚨 Problemas Comunes

| Problema                         | Causa                    | Solución                                  |
|----------------------------------|--------------------------|-------------------------------------------|
| Emails no se envían              | Worker no está corriendo | `php artisan queue:work`                  |
| Worker se detiene solo           | Sin supervisor           | Instalar y configurar Supervisor          |
| Jobs fallan siempre              | Error en el código       | `php artisan queue:failed` y revisar logs |
| Cola crece infinitamente         | Worker muy lento o caído | Aumentar `numprocs` en supervisor         |
| Cambios en código no se reflejan | Worker no reiniciado     | `php artisan queue:restart`               |

---

**¡Tu sistema de colas está listo!** 🎉

Para cualquier duda, ejecuta: `php artisan queue:verify`

