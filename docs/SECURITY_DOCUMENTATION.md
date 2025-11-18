# 🔐 Guía de Seguridad para Documentación

## ¿Qué documentación es segura para el repositorio?

### ✅ SEGURO - Incluir en el repositorio

#### Documentación Técnica

- ✅ Arquitectura y patrones de diseño
- ✅ Guías de uso de código
- ✅ Ejemplos de implementación
- ✅ Diagramas de flujo
- ✅ Documentación de APIs (sin credenciales)
- ✅ READMEs de paquetes
- ✅ Guías de testing
- ✅ Convenciones de código

#### Configuraciones de Ejemplo

- ✅ `.env.example` (sin valores reales)
- ✅ Ejemplos de configuración con placeholders
- ✅ Estructuras de archivos de configuración

#### Guías de Proceso

- ✅ Guías de migración
- ✅ Checklists de implementación
- ✅ Procedimientos de deploy (sin credenciales)
- ✅ Guías de monitoreo

---

### ⚠️ REVISAR - Verificar antes de incluir

- ⚠️ URLs de servicios (usar ejemplos genéricos)
- ⚠️ Nombres de recursos (pueden revelar estructura)
- ⚠️ Logs de errores (pueden contener datos sensibles)
- ⚠️ Capturas de pantalla (pueden mostrar datos reales)

---

### ❌ NUNCA - NO incluir en el repositorio

#### Credenciales y Claves

- ❌ API keys reales (Stripe, Redsys, etc.)
- ❌ Contraseñas o tokens de acceso
- ❌ Claves privadas (SSH, SSL, etc.)
- ❌ Webhooks secrets reales
- ❌ Archivos `.env` con valores de producción

#### Información del Cliente

- ❌ Datos personales de usuarios
- ❌ Información financiera
- ❌ Emails o teléfonos reales de clientes
- ❌ Números de cuenta bancaria
- ❌ Documentos legales con datos sensibles

#### URLs y Configuración de Producción

- ❌ URLs de producción específicas
- ❌ IPs de servidores
- ❌ Configuraciones de firewall
- ❌ Credenciales de base de datos
- ❌ Configuración de servicios cloud específicos

#### Documentos Internos

- ❌ Contratos comerciales
- ❌ Acuerdos de confidencialidad
- ❌ Presupuestos con precios reales
- ❌ Documentación con marcas de agua privadas

---

## 🛡️ Buenas Prácticas

### 1. Usar Placeholders

En lugar de valores reales:

```bash
# ❌ MAL
STRIPE_API_KEY=sk_live_51H5yxD2eZvKYlo2C...
DATABASE_URL=mysql://root:password123@192.168.1.100:3306/production_db

# ✅ BIEN
STRIPE_API_KEY=sk_live_YOUR_STRIPE_KEY_HERE
DATABASE_URL=mysql://user:password@host:3306/database
```

### 2. Usar Ejemplos Genéricos

```php
// ❌ MAL
$merchant_code = '357328590'; // Código real de Redsys

// ✅ BIEN
$merchant_code = config('redsys.merchant_code'); // Usa configuración
// O en documentación:
$merchant_code = '999999999'; // Código de ejemplo para tests
```

### 3. Sanitizar Capturas de Pantalla

Si incluyes imágenes:

- Difumina datos personales
- Oculta credenciales visibles
- Usa datos de prueba
- Revisa metadatos de la imagen

### 4. Revisar Antes de Commit

```bash
# Antes de hacer commit, verifica:
git diff docs/

# Busca patrones sensibles:
grep -r "password\|secret\|key\|token" docs/
grep -r "@gmail\|@hotmail" docs/
grep -r "192\.168\|10\.\|172\." docs/
```

---

## 📋 Checklist Pre-Commit

Antes de subir documentación al repositorio, verifica:

- [ ] No hay API keys reales
- [ ] No hay contraseñas
- [ ] URLs son genéricas o de ejemplo
- [ ] No hay datos de clientes reales
- [ ] Ejemplos de código usan placeholders
- [ ] Capturas de pantalla no muestran datos sensibles
- [ ] Archivos `.env` son solo ejemplos
- [ ] No hay información financiera real

---

## 🚨 Si Subes Información Sensible por Error

### Acción Inmediata

1. **NO hagas más commits encima**
2. **Contacta al administrador del repositorio**
3. **Cambia inmediatamente las credenciales expuestas**

### Limpieza del Historial

```bash
# Usar BFG Repo-Cleaner para eliminar del historial
# https://rtyley.github.io/bfg-repo-cleaner/

# O git filter-branch (más complejo)
git filter-branch --tree-filter 'rm -f docs/secret-file.md' HEAD
```

### Rotación de Credenciales

Si se expusieron credenciales:

1. ✅ Revoca las credenciales inmediatamente
2. ✅ Genera nuevas credenciales
3. ✅ Actualiza servicios con nuevas credenciales
4. ✅ Monitorea por uso no autorizado
5. ✅ Documenta el incidente

---

## 📁 Estructura Segura

```
docs/
├── README.md                    ✅ SEGURO
├── architecture/
│   └── GATEWAY_EXTENSIBILITY.md ✅ SEGURO (sin credenciales)
├── guides/
│   ├── DEPLOYMENT.md            ✅ SEGURO (con placeholders)
│   └── MONITORING.md            ✅ SEGURO
├── packages/
│   └── PACKAGES.md              ✅ SEGURO
└── examples/
    ├── .env.example             ✅ SEGURO (sin valores reales)
    └── config.example.php       ✅ SEGURO (valores de ejemplo)

# NO incluir:
❌ docs/credentials/
❌ docs/production-config/
❌ docs/client-data/
```

---

## 🔍 Herramientas de Detección

### Git Secrets

Previene commits con información sensible:

```bash
# Instalar
brew install git-secrets

# Configurar
git secrets --install
git secrets --register-aws
git secrets --add 'sk_live_[a-zA-Z0-9]+'
git secrets --add 'password.*=.*'
```

### Gitleaks

Escanea repositorio por secretos:

```bash
# Instalar
brew install gitleaks

# Escanear
gitleaks detect --source . --verbose
```

---

## 📖 Referencias

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [GitHub Security Best Practices](https://docs.github.com/en/code-security)
- [Git Secrets Tool](https://github.com/awslabs/git-secrets)

---

**Última actualización:** 18 de noviembre de 2025

**Recuerda:** La documentación técnica es valiosa y debe compartirse con el equipo.  
Solo asegúrate de que NO contenga información sensible real.

