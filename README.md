# Fundación Elena Tertre - Sistema de Gestión

Sistema web completo para la gestión de una fundación, incluyendo tienda solidaria, sistema de donaciones, gestión de
eventos y contenidos.

## 🚀 Características Principales

- 🛒 **Tienda Solidaria** con carrito de compras
- 💰 **Sistema de Donaciones** (únicas y recurrentes)
- 📅 **Gestión de Eventos**
- 📄 **CMS integrado** para páginas y contenidos
- 💳 **Múltiples pasarelas de pago** (Redsys, Stripe)
- 📊 **Panel de administración** (Filament)
- 🎨 **Diseño responsive** con Livewire + Flux UI

---

## 📚 Documentación

Toda la documentación del proyecto está organizada en el directorio [`docs/`](docs/):

- **[📖 Índice General](docs/README.md)** - Punto de inicio para toda la documentación
- **[🚀 Inicio Rápido](docs/START_HERE.md)** - Guía rápida para comenzar
- **[🏗️ Arquitectura](docs/architecture/)** - Decisiones de diseño y patrones
- **[📦 Paquetes](docs/packages/)** - Cartify y Payflow (paquetes reutilizables)
- **[📖 Guías](docs/guides/)** - Tutoriales y guías prácticas
- **[🔄 Migraciones](docs/migrations/)** - Historial de cambios estructurales

---

## 🛠️ Stack Tecnológico

- **Backend:** Laravel 12
- **Frontend:** Livewire 3 + Flux UI 2 + Tailwind CSS 4
- **Panel Admin:** Filament 3
- **Testing:** Pest 4
- **Base de Datos:** MySQL/SQLite
- **Pasarelas de Pago:** Redsys, Stripe (extensible)

---

## 📦 Paquetes Desarrollados

Este proyecto incluye dos paquetes Laravel independientes y reutilizables:

### 🛒 [Cartify](packages/cartify/)

Sistema completo de carrito de compras para Laravel.

```bash
composer require darkraul79/cartify
```

### 💳 [Payflow](packages/payflow/)

Sistema multi-pasarela de pagos con soporte para Redsys, Stripe y más.

```bash
composer require darkraul79/payflow
```

📖 **[Ver documentación completa de paquetes →](docs/packages/PACKAGES.md)**

---

## 🚀 Instalación

### Requisitos

- PHP 8.3+
- Composer
- Node.js 18+
- MySQL/SQLite

### Pasos

```bash
# Clonar repositorio
git clone https://github.com/darkraul79/fundacionelenatertre.git
cd fundacionelenatertre

# Instalar dependencias
composer install
npm install

# Configurar entorno
cp .env.example .env
php artisan key:generate

# Configurar base de datos en .env
# DB_CONNECTION=mysql
# DB_DATABASE=fundacion
# ...

# Ejecutar migraciones
php artisan migrate --seed

# Compilar assets
npm run build

# Iniciar servidor
php artisan serve
```

---

## 🧪 Tests

```bash
# Ejecutar todos los tests
php artisan test

# Tests específicos
php artisan test --filter=OrderTest
php artisan test --group=performance
php artisan test --group=observability
php artisan test --group=gateways

# Con cobertura
php artisan test --coverage
```

**Estado actual:** 144+ tests, todas las suites pasando ✅

---

## 🎯 Funcionalidades

### Para Usuarios

- ✅ Navegación de productos y eventos
- ✅ Carrito de compras con múltiples productos
- ✅ Donaciones únicas o recurrentes
- ✅ Pago seguro con Redsys/Bizum
- ✅ Certificados de donación
- ✅ Gestión de direcciones de envío

### Para Administradores (Filament)

- ✅ Gestión de productos y stock
- ✅ Gestión de pedidos y donaciones
- ✅ Gestión de eventos
- ✅ CMS para páginas y contenidos
- ✅ Informes y estadísticas
- ✅ Gestión de usuarios

---

## 📖 Guías Rápidas

- **[Añadir nueva pasarela de pago](docs/architecture/GATEWAY_EXTENSIBILITY.md)**
- **[Publicar paquetes en GitHub](docs/packages/GITHUB_PUBLISHING_GUIDE.md)**
- **[Usar paquetes en otros proyectos](docs/packages/HOW_TO_USE_IN_OTHER_PROJECTS.md)**
- **[Monitoreo de colas](docs/guides/QUEUE_MONITORING_GUIDE.md)**

---

## 🔐 Seguridad

Si descubres algún problema de seguridad, por favor envía un email a **info@raulsebastian.es** en lugar de usar el issue
tracker.

### Auditoría de Seguridad

Este repositorio ha sido auditado y es **seguro para publicación**.
Ver [Reporte de Auditoría](docs/SECURITY_AUDIT_REPORT.md).

```bash
# Ejecutar verificación de seguridad antes de cada commit importante
./security-check.sh
```

### Guías de Seguridad

- **[Guía de Seguridad para Documentación](docs/SECURITY_DOCUMENTATION.md)** - Qué es seguro incluir en el repositorio
- **[Reporte de Auditoría](docs/SECURITY_AUDIT_REPORT.md)** - Última auditoría realizada

⚠️ **Importante:** Las credenciales reales deben estar SOLO en `.env` (nunca en Git)


---

## 📝 Licencia

Este proyecto es privado y propietario de la Fundación Elena Tertre.

---

## 👨‍💻 Desarrollo

**Autor:** Raúl Sebastián (@darkraul79)  
**Versión:** 0.1.0 Alpha  
**Última actualización:** 18 de noviembre de 2025

---

## 🤝 Contribución

Para contribuir al proyecto:

1. Lee la [documentación completa](docs/README.md)
2. Revisa las [guías de implementación](docs/guides/)
3. Ejecuta los tests antes de hacer commits
4. Sigue las convenciones de código (Laravel Pint)

---

## 📧 Contacto

- **Web:** [www.fundacionelenatertre.org](https://www.fundacionelenatertre.org)
- **Email:** info@raulsebastian.es
- **GitHub:** [@darkraul79](https://github.com/darkraul79)

---

**[📚 Ver documentación completa →](docs/README.md)**

