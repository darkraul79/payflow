# 📋 Guía Paso a Paso para Publicar en GitHub

## 🎯 Objetivo

Publicar **Cartify** y **Payflow** en GitHub y Packagist para que sean públicos y cualquiera pueda usarlos.

---

## ✅ Pre-requisitos

- [x] Cuenta en GitHub
- [x] Cuenta en Packagist (https://packagist.org)
- [x] Git instalado
- [x] Paquetes listos en `packages/cartify` y `packages/payflow`

---

## 📦 Paso 1: Crear Repositorio para Cartify

### 1.1 En GitHub

1. Ve a https://github.com/new
2. Configuración:
    - **Repository name:** `cartify`
    - **Description:** `A flexible and powerful shopping cart package for Laravel`
    - **Public** ✅
    - **NO** marcar "Initialize this repository with a README"
    - **License:** MIT
3. Click "Create repository"

### 1.2 En Local (Terminal)

```bash
cd packages/cartify

# Inicializar repositorio
git init

# Crear README si no existe (ya existe)
# git add README.md

# Agregar todos los archivos
git add .

# Primer commit
git commit -m "Initial release v1.0.0

- Shopping cart management
- Multiple cart instances
- User persistence
- Automatic calculations
- Database migrations
- Complete documentation"

# Configurar rama principal
git branch -M main

# Conectar con GitHub
git remote add origin https://github.com/raulsdev/cartify.git

# Subir código
git push -u origin main
```

---

## 📦 Paso 2: Crear Repositorio para Payflow

### 2.1 En GitHub

1. Ve a https://github.com/new
2. Configuración:
    - **Repository name:** `payflow`
    - **Description:** `A flexible multi-gateway payment package for Laravel (Redsys, Stripe, PayPal)`
    - **Public** ✅
    - **NO** marcar "Initialize this repository with a README"
    - **License:** MIT
3. Click "Create repository"

### 2.2 En Local (Terminal)

```bash
cd ../payflow  # Desde cartify

# Inicializar repositorio
git init

# Agregar todos los archivos
git add .

# Primer commit
git commit -m "Initial release v1.0.0

- Unified payment gateway interface
- Redsys fully implemented (Bizum, recurring payments)
- Transaction logging
- Refund management
- Database migrations
- Complete documentation"

# Configurar rama principal
git branch -M main

# Conectar con GitHub
git remote add origin https://github.com/raulsdev/payflow.git

# Subir código
git push -u origin main
```

---

## 🏷️ Paso 3: Crear Releases en GitHub

### 3.1 Release para Cartify

1. Ve a https://github.com/raulsdev/cartify
2. Click en "Releases" (barra lateral derecha)
3. Click "Create a new release"
4. Configuración:
    - **Tag:** `v1.0.0`
    - **Target:** `main`
    - **Release title:** `v1.0.0 - Initial Release`
    - **Description:**
      ```markdown
      ## 🎉 Initial Release
      
      Cartify is a flexible and powerful shopping cart package for Laravel.
      
      ### Features
      - ✅ Shopping cart management with session storage
      - ✅ Multiple cart instances (cart, wishlist, etc.)
      - ✅ User persistence (store/restore/merge)
      - ✅ Automatic calculations (subtotal, tax, total)
      - ✅ Database migrations included
      - ✅ Helper functions
      - ✅ Complete documentation
      
      ### Installation
      
      ```bash
      composer require raulsdev/cartify
      ```

      ### Documentation

      See [README.md](https://github.com/raulsdev/cartify/blob/main/README.md) for complete documentation.
      ```
5. Click "Publish release"

### 3.2 Release para Payflow

1. Ve a https://github.com/raulsdev/payflow
2. Click en "Releases"
3. Click "Create a new release"
4. Configuración:
    - **Tag:** `v1.0.0`
    - **Target:** `main`
    - **Release title:** `v1.0.0 - Initial Release`
    - **Description:**
      ```markdown
      ## 🎉 Initial Release
      
      Payflow is a flexible multi-gateway payment package for Laravel.
      
      ### Features
      - ✅ Unified API for multiple payment gateways
      - ✅ **Redsys fully implemented** (Spain's leading payment gateway)
      - ✅ Bizum support (instant mobile payments)
      - ✅ Recurring payments
      - ✅ Automatic signature verification
      - ✅ Transaction logging to database
      - ✅ Refund management
      - ✅ Complete documentation
      
      ### Supported Gateways
      - ✅ **Redsys** (Production ready)
      - 🚧 **Stripe** (Coming soon)
      - 🚧 **PayPal** (Coming soon)
      
      ### Installation
      
      ```bash
      composer require raulsdev/payflow
      ```

      ### Documentation

      See [README.md](https://github.com/raulsdev/payflow/blob/main/README.md) for complete documentation.
      ```
5. Click "Publish release"

---

## 📦 Paso 4: Registrar en Packagist

### 4.1 Crear cuenta en Packagist (si no tienes)

1. Ve a https://packagist.org/register/
2. Regístrate con tu email
3. Confirma tu email

### 4.2 Registrar Cartify

1. Ve a https://packagist.org/packages/submit
2. En "Repository URL" ingresa:
   ```
   https://github.com/raulsdev/cartify
   ```
3. Click "Check"
4. Si todo está correcto, click "Submit"
5. ✅ Cartify ahora está en Packagist!

### 4.3 Registrar Payflow

1. Ve a https://packagist.org/packages/submit
2. En "Repository URL" ingresa:
   ```
   https://github.com/raulsdev/payflow
   ```
3. Click "Check"
4. Si todo está correcto, click "Submit"
5. ✅ Payflow ahora está en Packagist!

---

## 🔄 Paso 5: Configurar Auto-Update en Packagist

### 5.1 GitHub Webhook (Recomendado)

Para que Packagist se actualice automáticamente cuando haces push:

1. En Packagist, ve a tu paquete
2. Click en tu nombre de usuario → "Profile" → "Show API Token"
3. Copia el token

#### Para Cartify:

1. Ve a https://github.com/raulsdev/cartify/settings/hooks
2. Click "Add webhook"
3. Configuración:
    - **Payload URL:** `https://packagist.org/api/github?username=raulsdev`
    - **Content type:** `application/json`
    - **Secret:** (tu API token de Packagist)
    - **Events:** "Just the push event"
4. Click "Add webhook"

#### Para Payflow:

1. Ve a https://github.com/raulsdev/payflow/settings/hooks
2. Repite el mismo proceso

---

## ✅ Paso 6: Verificar Instalación

Prueba que todo funcione:

```bash
# Crear nuevo proyecto Laravel
composer create-project laravel/laravel test-packages
cd test-packages

# Instalar paquetes
composer require raulsdev/cartify
composer require raulsdev/payflow

# Publicar configuraciones
php artisan vendor:publish --provider="Raulsdev\Cartify\CartifyServiceProvider"
php artisan vendor:publish --provider="Raulsdev\Payflow\PayflowServiceProvider"

# Ejecutar migraciones
php artisan migrate

# Probar en tinker
php artisan tinker
```

En tinker:

```php
use Raulsdev\Cartify\Facades\Cart;
Cart::add(1, 'Test', 1, 29.99);
Cart::content();
```

---

## 🎨 Paso 7: Personalizar GitHub (Opcional)

### 7.1 Agregar Badges al README

En ambos repositorios, puedes agregar badges al inicio del README:

```markdown
[![Latest Version](https://img.shields.io/packagist/v/raulsdev/cartify)](https://packagist.org/packages/raulsdev/cartify)
[![Total Downloads](https://img.shields.io/packagist/dt/raulsdev/cartify)](https://packagist.org/packages/raulsdev/cartify)
[![License](https://img.shields.io/packagist/l/raulsdev/cartify)](https://packagist.org/packages/raulsdev/cartify)
```

### 7.2 Agregar Topics en GitHub

En cada repositorio:

1. Click en ⚙️ (settings icon) al lado de "About"
2. Agregar topics:
    - Para Cartify: `laravel`, `cart`, `shopping-cart`, `e-commerce`, `php`
    - Para Payflow: `laravel`, `payment`, `redsys`, `stripe`, `gateway`, `php`

### 7.3 Agregar Descripción y Website

- **Cartify:**
    - Description: `A flexible and powerful shopping cart package for Laravel`
    - Website: `https://packagist.org/packages/raulsdev/cartify`

- **Payflow:**
    - Description: `A flexible multi-gateway payment package for Laravel`
    - Website: `https://packagist.org/packages/raulsdev/payflow`

---

## 📢 Paso 8: Promocionar (Opcional)

### Reddit

- r/PHP
- r/laravel

### Twitter/X

```
🎉 Just released two Laravel packages:

🛒 Cartify - Shopping cart with multiple instances, persistence & more
💳 Payflow - Multi-gateway payments (Redsys, Stripe, PayPal)

Both include migrations, helpers & full docs.

github.com/raulsdev/cartify
github.com/raulsdev/payflow

#Laravel #PHP
```

### Laravel News

Puedes enviar tu paquete para que lo mencionen: https://laravel-news.com/submit

---

## 🆘 Solución de Problemas

### Error: "Could not authenticate"

- Verifica que tu token de GitHub tenga los permisos correctos
- Regenera el token si es necesario

### Error en Packagist: "Could not find a composer.json"

- Asegúrate de que el archivo `composer.json` esté en la raíz del repositorio
- Verifica que el repositorio sea público

### El webhook no funciona

- Ve a Settings → Webhooks en GitHub
- Click en el webhook
- Ve a "Recent Deliveries" para ver los errores

---

## ✅ Checklist Final

- [ ] Cartify en GitHub
- [ ] Payflow en GitHub
- [ ] Release v1.0.0 de Cartify
- [ ] Release v1.0.0 de Payflow
- [ ] Cartify en Packagist
- [ ] Payflow en Packagist
- [ ] Webhooks configurados
- [ ] Instalación probada
- [ ] Badges agregados (opcional)
- [ ] Topics agregados (opcional)
- [ ] Promocionado (opcional)

---

## 🎉 ¡Felicidades!

Tus paquetes ahora son públicos y cualquiera puede usarlos con:

```bash
composer require raulsdev/cartify
composer require raulsdev/payflow
```

---

## 📞 Contacto y Soporte

- GitHub Issues: Para reportar bugs o sugerir features
- Pull Requests: ¡Contribuciones son bienvenidas!
- Discussions: Para preguntas generales

---

**¡Gracias por compartir con la comunidad Laravel!** 🚀

