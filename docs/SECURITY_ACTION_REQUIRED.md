# ⚠️ ACCIÓN INMEDIATA REQUERIDA - Seguridad

## 🚨 Credenciales Encontradas y Corregidas

Durante la auditoría de seguridad, se encontraron **credenciales reales de Redsys hardcodeadas** en el archivo
`config/redsys.php`.

### Estado: ✅ CORREGIDO

Las credenciales han sido eliminadas del código, pero **debes tomar medidas adicionales**.

---

## 📋 CHECKLIST DE ACCIONES INMEDIATAS

### 1. ✅ Rotar Credenciales de Redsys (RECOMENDADO)

Aunque las credenciales **NO estaban en el historial de Git**, es buena práctica de seguridad rotarlas:

#### Pasos:

1. **Accede al panel de Redsys:**
    - URL: https://canales.redsys.es
    - Usuario: Tu usuario de comercio

2. **Genera nueva clave SHA-256:**
    - Ve a "Administración" → "Claves SHA-256"
    - Genera nueva clave
    - **Guarda la nueva clave de forma segura**

3. **Actualiza tu archivo `.env` local:**
   ```bash
   # Edita .env (NUNCA commitear este archivo)
   REDSYS_KEY=<nueva_clave_generada>
   REDSYS_MERCHANT_CODE=357328590
   ```

4. **Verifica que funciona:**
   ```bash
   # Ejecutar tests
   php artisan test tests/Unit/PaymentTest.php
   
   # Probar una donación/pedido en entorno de test
   ```

5. **Revoca la clave antigua** (opcional pero recomendado)

---

### 2. ✅ Verificar Archivo `.env`

Asegúrate de que tu `.env` local tiene las credenciales correctas:

```bash
# Verifica que .env existe y contiene:
cat .env | grep REDSYS

# Debe mostrar algo como:
# REDSYS_KEY=tu_clave_aquí
# REDSYS_MERCHANT_CODE=tu_codigo_comercio
```

⚠️ **IMPORTANTE:** El archivo `.env` **NUNCA** debe estar en Git.

---

### 3. ✅ Ejecutar Verificación de Seguridad

```bash
# Ejecuta el script de verificación
./security-check.sh
```

Debe mostrar: `✅ ¡PERFECTO! No se encontraron problemas de seguridad`

---

### 4. ✅ Antes de Hacer Commit

```bash
# 1. Verifica cambios
git status
git diff

# 2. Ejecuta verificación de seguridad
./security-check.sh

# 3. Si todo está OK, haz commit
git add config/redsys.php .env.example README.md docs/ security-check.sh
git commit -m "security: eliminar credenciales hardcodeadas y añadir auditoría de seguridad"

# 4. NO hagas push hasta verificar que:
#    - .env NO está en los cambios
#    - config/redsys.php NO tiene credenciales
#    - ./security-check.sh pasa sin errores
```

---

## 📊 ¿Qué se Corrigió?

### Antes (❌ INSEGURO):

```php
// config/redsys.php
return [
    'key' => env('REDSYS_KEY', 'your_actual_key_here'),  // ❌
    'merchantcode' => env('REDSYS_MERCHANT_CODE', '999999999'),        // ❌
];
```

### Después (✅ SEGURO):

```php
// config/redsys.php
return [
    'key' => env('REDSYS_KEY'),           // ✅ Sin valor por defecto
    'merchantcode' => env('REDSYS_MERCHANT_CODE'), // ✅ Sin valor por defecto
];
```

```dotenv
# .env.example (ejemplo público)
REDSYS_KEY=your_merchant_key_here
REDSYS_MERCHANT_CODE=999999999
```

```dotenv
# .env (local, NO en Git)
REDSYS_KEY=your_actual_merchant_key_here
REDSYS_MERCHANT_CODE=your_merchant_code_here
```

---

## 🛡️ Prevención Futura

### Instalar git-secrets (Opcional pero Recomendado)

```bash
# Instalar
brew install git-secrets

# Configurar en este repositorio
cd /Users/raulsebastian/PhpstormProjects/fundacionelenatertre
git secrets --install

# Añadir patrones para detectar
git secrets --add 'your_actual_merchant_key_here'
git secrets --add 'your_merchant_code_here'
git secrets --add 'sk_live_[a-zA-Z0-9]+'
git secrets --add 'sk_test_[a-zA-Z0-9]+'
```

### Pre-commit Hook Automático

Ya se creó `security-check.sh`. Para ejecutarlo automáticamente:

```bash
# Crear hook
cat > .git/hooks/pre-commit << 'EOF'
#!/bin/bash
./security-check.sh
if [ $? -ne 0 ]; then
    echo ""
    echo "❌ Commit cancelado por problemas de seguridad"
    exit 1
fi
EOF

chmod +x .git/hooks/pre-commit
```

---

## 📖 Documentación Creada

1. **[docs/SECURITY_AUDIT_REPORT.md](SECURITY_AUDIT_REPORT.md)**  
   Reporte completo de la auditoría realizada

2. **[docs/SECURITY_DOCUMENTATION.md](SECURITY_DOCUMENTATION.md)**  
   Guía de qué es seguro incluir en el repositorio

3. **[security-check.sh](../security-check.sh)**  
   Script para verificar seguridad antes de commits

4. **README.md**  
   Actualizado con sección de seguridad

---

## ❓ Preguntas Frecuentes

### ¿Puedo publicar este repositorio en GitHub ahora?

✅ **SÍ**, después de:

1. Rotar las credenciales de Redsys (recomendado)
2. Verificar que `./security-check.sh` pasa
3. Confirmar que `.env` NO está en Git

### ¿Las credenciales estaban en el historial de Git?

✅ **NO**, se verificó el historial completo y las credenciales NO fueron commiteadas.

### ¿Qué pasa si ya publiqué el repositorio?

⚠️ Si el repositorio ya está público:

1. Rota las credenciales INMEDIATAMENTE
2. Considera hacer el repositorio privado temporalmente
3. Sigue esta guía para corregir
4. Vuelve a publicar

### ¿Debo cambiar las credenciales ahora?

✅ **Recomendado**, aunque no es crítico ya que:

- Las credenciales NO estaban en Git
- El archivo fue corregido antes de cualquier commit
- Es buena práctica rotar credenciales periódicamente

---

## 📞 Contacto

Si tienes dudas sobre seguridad:

- **Email:** info@raulsebastian.es
- **Documentación:** [docs/SECURITY_DOCUMENTATION.md](SECURITY_DOCUMENTATION.md)

---

**Fecha de este reporte:** 18 de noviembre de 2025  
**Estado:** ✅ CORREGIDO  
**Acción requerida:** Rotar credenciales de Redsys (recomendado)

