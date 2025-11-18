#!/bin/bash

# 🔐 Script de Verificación de Seguridad
# Ejecutar antes de cada commit importante o publicación

echo "🔐 Iniciando Auditoría de Seguridad..."
echo ""

# Colores
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

errors=0
warnings=0

# 1. Verificar archivos .env
echo "📁 Verificando archivos .env..."
if git ls-files | grep -E "^\.env$|\.env\.production|\.env\.local"; then
    echo -e "${RED}❌ ERROR: Archivos .env encontrados en Git${NC}"
    git ls-files | grep -E "^\.env$|\.env\.production|\.env\.local"
    ((errors++))
else
    echo -e "${GREEN}✅ No hay archivos .env en Git${NC}"
fi
echo ""

# 2. Buscar API keys de Stripe
echo "🔑 Buscando API keys de Stripe..."
if grep -r "sk_live_[A-Za-z0-9]\|sk_test_[A-Za-z0-9]\|pk_live_[A-Za-z0-9]\|pk_test_[A-Za-z0-9]" --include="*.php" --include="*.env" . 2>/dev/null | grep -v "vendor/" | grep -v "node_modules/" | grep -v "example" | grep -v "YOUR_" | grep -v "sk_live_\[" | grep -v "sk_test_\["; then
    echo -e "${RED}❌ ERROR: Posibles API keys de Stripe encontradas${NC}"
    ((errors++))
else
    echo -e "${GREEN}✅ No se encontraron API keys de Stripe${NC}"
fi
echo ""

# 3. Buscar credenciales de Redsys
echo "💳 Buscando credenciales de Redsys..."
# Buscar patrones de claves Redsys (base64-like strings largas) pero excluir ejemplos
if grep -rE "'[A-Za-z0-9]{30,}'" --include="*.php" config/redsys.php 2>/dev/null | grep -v "env(" | grep -v "example"; then
    echo -e "${RED}❌ ERROR: Posibles credenciales hardcodeadas encontradas${NC}"
    ((errors++))
else
    echo -e "${GREEN}✅ No se encontraron credenciales hardcodeadas${NC}"
fi
echo ""

# 4. Buscar contraseñas hardcodeadas
echo "🔒 Buscando contraseñas hardcodeadas..."
matches=$(grep -rE "password\s*=\s*['\"][^'\"]+['\"]" --include="*.php" . 2>/dev/null | grep -v "vendor/" | grep -v "node_modules/" | grep -v "example" | grep -v "password" | grep -v "YOUR_" | wc -l)
if [ $matches -gt 0 ]; then
    echo -e "${YELLOW}⚠️  ADVERTENCIA: Posibles contraseñas encontradas ($matches)${NC}"
    ((warnings++))
else
    echo -e "${GREEN}✅ No se encontraron contraseñas hardcodeadas${NC}"
fi
echo ""

# 5. Verificar .gitignore
echo "📋 Verificando .gitignore..."
critical_entries=(".env" ".env.production" ".env.local" )
missing=0
for entry in "${critical_entries[@]}"; do
    if ! grep -q "^${entry}\$\|^/${entry}\$\|^\\${entry}\$" .gitignore; then
        echo -e "${RED}❌ ERROR: ${entry} no está en .gitignore${NC}"
        ((errors++))
        ((missing++))
    fi
done
if [ $missing -eq 0 ]; then
    echo -e "${GREEN}✅ .gitignore contiene entradas críticas${NC}"
fi
echo ""

# 6. Verificar archivos sensibles trackeados
echo "🔍 Verificando archivos sensibles en Git..."
if git ls-files | grep -E "\.env$|\.env\.production|\.env\.local|auth\.json|credentials"; then
    echo -e "${RED}❌ ERROR: Archivos sensibles trackeados en Git${NC}"
    ((errors++))
else
    echo -e "${GREEN}✅ No hay archivos sensibles trackeados${NC}"
fi
echo ""

# 7. Verificar configuración de Redsys
echo "⚙️  Verificando config/redsys.php..."
if grep -E "env\('REDSYS_KEY',\s*'[^']+'\)" config/redsys.php 2>/dev/null; then
    echo -e "${RED}❌ ERROR: Credenciales hardcodeadas en config/redsys.php${NC}"
    ((errors++))
else
    echo -e "${GREEN}✅ config/redsys.php usa solo env()${NC}"
fi
echo ""

# 8. Verificar .env.example
echo "📝 Verificando .env.example..."
if [ -f .env.example ]; then
    # Buscar claves que parezcan reales (largas, base64-like)
    # Excluir placeholders comunes
    if grep -E "REDSYS_KEY=[A-Za-z0-9]{25,}|STRIPE_API_KEY=sk_live_" .env.example 2>/dev/null | grep -v "your_merchant_key_here" | grep -v "your_"; then
        echo -e "${RED}❌ ERROR: .env.example contiene credenciales reales${NC}"
        ((errors++))
    else
        echo -e "${GREEN}✅ .env.example solo contiene placeholders${NC}"
    fi
else
    echo -e "${YELLOW}⚠️  ADVERTENCIA: .env.example no existe${NC}"
    ((warnings++))
fi
echo ""

# Resumen
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "📊 RESUMEN DE AUDITORÍA"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

if [ $errors -eq 0 ] && [ $warnings -eq 0 ]; then
    echo -e "${GREEN}✅ ¡PERFECTO! No se encontraron problemas de seguridad${NC}"
    echo ""
    echo "El repositorio es seguro para:"
    echo "  ✅ Publicación en GitHub"
    echo "  ✅ Compartir con colaboradores"
    echo "  ✅ Deploy en producción"
    exit 0
elif [ $errors -eq 0 ]; then
    echo -e "${YELLOW}⚠️  ADVERTENCIAS: $warnings${NC}"
    echo -e "${GREEN}❌ ERRORES: 0${NC}"
    echo ""
    echo "El repositorio es seguro pero revisa las advertencias"
    exit 0
else
    echo -e "${RED}❌ ERRORES CRÍTICOS: $errors${NC}"
    echo -e "${YELLOW}⚠️  ADVERTENCIAS: $warnings${NC}"
    echo ""
    echo -e "${RED}⛔ El repositorio NO es seguro para publicación${NC}"
    echo ""
    echo "Acciones recomendadas:"
    echo "  1. Revisa y corrige los errores listados arriba"
    echo "  2. Lee docs/SECURITY_DOCUMENTATION.md"
    echo "  3. Vuelve a ejecutar este script"
    exit 1
fi

