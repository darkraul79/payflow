# ✅ Lista de Verificación de Seguridad del Repositorio

## Estado Actual: ✅ LIMPIO

Fecha de última verificación: 2025-11-18

---

## 1. Verificación de Archivos Sensibles en .gitignore

### ✅ Archivos Protegidos Correctamente

```bash
# Verificar que .gitignore contiene:
.env
.env.backup
.env.production
.env.local
auth.json
.auth.json
/.mcp.json
/.claude/
/.junie/
```

**Comando de verificación:**

```bash
git status --ignored | grep -E "\.env|auth\.json|\.mcp\.json"
```

---

## 2. Búsqueda de Credenciales en Historial de Git

### ✅ Sin Credenciales Detectadas

**Comandos ejecutados:**

```bash
# Buscar archivos .env en historial
git log --all --full-history -- .env

# Buscar claves específicas en todo el historial
git grep -i "MERCHANT_KEY\|merchant_key\|FUC\|secret" $(git rev-list --all)

# Buscar valores específicos de claves de prueba
git log --all --patch -S "Sq7HjrUOBfKmC576ILgskD5srU870gJ7"
```

**Resultado:** ✅ No se encontraron credenciales reales en el historial

---

## 3. Archivos que DEBEN Estar en el Repositorio

### ✅ Archivos Seguros Incluidos

- `/.env.example` - ✅ Plantilla sin valores reales
- `/config/*.php` - ✅ Solo referencias a env()
- `/docs/` - ✅ Documentación técnica sin credenciales
- `SECURITY_ACTION_REQUIRED.md` - ✅ Guía de configuración
- `SECURITY_AUDIT.md` - ✅ Auditoría de seguridad

---

## 4. Verificación de Archivos de Configuración

### Archivos Revisados:

#### ✅ config/redsys.php

```php
'merchant_key' => env('REDSYS_KEY'), // ✅ Usa variable de entorno
'merchant_code' => env('REDSYS_FUC'), // ✅ Usa variable de entorno
```

#### ✅ .env.example

```env
REDSYS_KEY=your_merchant_key_here  # ✅ Placeholder genérico
REDSYS_FUC=your_merchant_code_here # ✅ Placeholder genérico
```

---

## 5. Comandos de Verificación Recomendados

### Antes de Cada Commit

```bash
# 1. Verificar que no hay archivos .env sin rastrear
git status | grep -E "\.env$"

# 2. Verificar que no hay credenciales en archivos staged
git diff --cached | grep -iE "(merchant.*key|api.*key|secret|password.*=)"

# 3. Revisar archivos que se van a commitear
git diff --cached --name-only
```

### Verificación Periódica del Repositorio

```bash
# 1. Buscar patrones sensibles en archivos rastreados
git grep -iE "(merchant_key|api_key|secret).*=.*['\"][^your_]" -- '*.php' '*.env.example'

# 2. Verificar que .env está ignorado
git check-ignore .env

# 3. Listar archivos ignorados
git status --ignored
```

---

## 6. Herramientas de Seguridad (Opcional)

### git-secrets (Recomendado para Protección Extra)

```bash
# Instalación en macOS
brew install git-secrets

# Configuración en el repositorio
cd /ruta/a/tu/proyecto
git secrets --install
git secrets --register-aws

# Agregar patrones personalizados
git secrets --add 'Sq7HjrUOBfKmC576ILgskD5srU870gJ7'
git secrets --add '[0-9]{9}' # Para FUC (9 dígitos)
git secrets --add 'sk_live_[a-zA-Z0-9]+'
git secrets --add 'sk_test_[a-zA-Z0-9]+'

# Escanear todo el repositorio
git secrets --scan-history
```

### gitleaks (Alternativa Avanzada)

```bash
# Instalación
brew install gitleaks

# Escanear repositorio
gitleaks detect --source . --verbose

# Escanear historial completo
gitleaks detect --source . --log-opts="--all"
```

---

## 7. Pre-commit Hook (Prevención Automática)

### Crear Pre-commit Hook

```bash
# Crear el archivo
cat > .git/hooks/pre-commit << 'EOF'
#!/bin/bash

# Buscar patrones sensibles antes del commit
if git diff --cached | grep -qiE "(Sq7HjrUOBfKmC576ILgskD5srU870gJ7|REDSYS_KEY=.{30,}|merchant_key.*=.*['\"](?!env\(|your_))"; then
    echo "❌ ERROR: Posible credencial detectada en el commit"
    echo "Revisa los archivos staged para asegurar que no contienen información sensible"
    exit 1
fi

# Verificar que no se intenta commitear .env
if git diff --cached --name-only | grep -qE "^\.env$"; then
    echo "❌ ERROR: Intentando commitear archivo .env"
    echo "Este archivo debe permanecer local y no subirse al repositorio"
    exit 1
fi

echo "✅ Verificación de seguridad pasada"
EOF

# Dar permisos de ejecución
chmod +x .git/hooks/pre-commit
```

---

## 8. Qué Hacer Si Encuentras Credenciales en el Historial

### 🚨 Procedimiento de Emergencia

Si alguna vez se encuentran credenciales reales en el historial:

#### Paso 1: Cambiar Credenciales Inmediatamente

```bash
# 1. Ir al portal de Redsys
# 2. Generar nuevas claves
# 3. Actualizar .env local con las nuevas claves
```

#### Paso 2: Limpiar el Historial de Git

```bash
# ADVERTENCIA: Esto reescribe el historial de Git

# Opción A: Usando git filter-repo (Recomendado)
pip install git-filter-repo
git filter-repo --invert-paths --path .env

# Opción B: Usando BFG Repo-Cleaner
java -jar bfg.jar --delete-files .env
git reflog expire --expire=now --all
git gc --prune=now --aggressive

# Opción C: Usando git filter-branch (Método Manual)
git filter-branch --force --index-filter \
  "git rm --cached --ignore-unmatch .env" \
  --prune-empty --tag-name-filter cat -- --all
```

#### Paso 3: Forzar Push (Solo si es Necesario)

```bash
# ADVERTENCIA: Esto afectará a todos los colaboradores
git push --force --all
git push --force --tags
```

#### Paso 4: Notificar al Equipo

- Informar a todos los colaboradores
- Pedir que hagan un nuevo clone del repositorio
- Verificar que todos actualicen sus credenciales locales

---

## 9. Lista de Archivos Sensibles a Vigilar

### ❌ NUNCA Deben Estar en Git:

- `.env`
- `.env.local`
- `.env.production`
- `auth.json`
- `.auth.json`
- `/.mcp.json` (puede contener API keys)
- Cualquier archivo con sufijo `.secret` o `.key`

### ✅ SEGUROS para Git:

- `.env.example`
- `config/*.php` (que usen env())
- `docs/*.md`
- `SECURITY_*.md`
- Tests con datos de prueba públicos

---

## 10. Verificación Final Antes de Hacer Push

### Checklist Pre-Push:

```bash
# 1. ✅ .env está en .gitignore
grep -q "^\.env$" .gitignore && echo "✅ .env en .gitignore" || echo "❌ FALTA .env en .gitignore"

# 2. ✅ No hay archivos .env en staged
! git diff --cached --name-only | grep -qE "\.env$" && echo "✅ Sin .env en staged" || echo "❌ HAY .env en staged"

# 3. ✅ .env.example no tiene credenciales reales
! grep -qE "(Sq7HjrUOBfKmC576ILgskD5srU870gJ7|[0-9]{9})" .env.example && echo "✅ .env.example limpio" || echo "❌ .env.example tiene credenciales"

# 4. ✅ Configs usan env()
grep -r "env('REDSYS_KEY')" config/ && echo "✅ Configs usan env()" || echo "⚠️ Revisar configs"

# 5. ✅ No hay claves hardcodeadas en el código
! git grep -iE "'(merchant_key|api_key|secret)'.*=>.*'[a-zA-Z0-9]{20,}'" -- '*.php' && echo "✅ Sin claves hardcodeadas" || echo "❌ HAY claves hardcodeadas"
```

---

## 11. Monitoreo Continuo

### Revisar Periódicamente:

- **Semanal:** Ejecutar `git status --ignored`
- **Antes de cada release:** Ejecutar verificación completa con gitleaks
- **Después de onboarding:** Revisar que nuevos devs configuren .env local
- **Tras cambios en config:** Verificar que se mantengan referencias a env()

---

## 12. Documentación de Incidentes

### Historial de Auditorías:

| Fecha      | Acción                                  | Resultado    | Responsable |
|------------|-----------------------------------------|--------------|-------------|
| 2025-11-18 | Auditoría inicial completa              | ✅ Limpio     | Sistema     |
| 2025-11-18 | Creación de SECURITY_ACTION_REQUIRED.md | ✅ Completado | Sistema     |
| 2025-11-18 | Creación de SECURITY_AUDIT.md           | ✅ Completado | Sistema     |
| 2025-11-18 | Creación de checklist de verificación   | ✅ Completado | Sistema     |

---

## 13. Recursos Adicionales

### Enlaces Útiles:

- [GitHub: Removing sensitive data](https://docs.github.com/en/authentication/keeping-your-account-and-data-secure/removing-sensitive-data-from-a-repository)
- [git-secrets en GitHub](https://github.com/awslabs/git-secrets)
- [gitleaks en GitHub](https://github.com/gitleaks/gitleaks)
- [BFG Repo-Cleaner](https://rtyley.github.io/bfg-repo-cleaner/)

---

## ✅ Estado Actual del Repositorio

**Verificado el:** 2025-11-18

- ✅ `.env` está en `.gitignore`
- ✅ No hay credenciales en el historial de Git
- ✅ `.env.example` solo contiene placeholders
- ✅ Archivos `config/*.php` usan `env()` correctamente
- ✅ Documentación creada sin información sensible
- ✅ Archivos sensibles protegidos en `.gitignore`

**Conclusión:** El repositorio está limpio y seguro para compartir públicamente.

---

## 🔐 Recordatorios Importantes

1. **Nunca** commitees archivos `.env`
2. **Siempre** verifica con `git diff --cached` antes de commitear
3. **Usa** `env()` en configuraciones, nunca valores hardcodeados
4. **Mantén** `.env.example` actualizado pero sin valores reales
5. **Revisa** periódicamente con herramientas como gitleaks
6. **Cambia** las credenciales inmediatamente si se detecta una filtración

---

**Última actualización:** 2025-11-18
**Próxima revisión:** Antes del próximo release

