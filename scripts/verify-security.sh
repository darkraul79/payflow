#!/bin/bash

# Script de Verificación de Seguridad del Repositorio
# Ejecutar antes de hacer push o periódicamente

echo "🔐 Iniciando verificación de seguridad del repositorio..."
echo ""

# Colores para output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

ERRORS=0
WARNINGS=0

# 1. Verificar que .env está en .gitignore
echo "📋 1. Verificando .gitignore..."
if grep -q "^\.env$" .gitignore; then
    echo -e "${GREEN}✅ .env está en .gitignore${NC}"
else
    echo -e "${RED}❌ FALTA .env en .gitignore${NC}"
    ((ERRORS++))
fi

# 2. Verificar que .env no está en staged
echo ""
echo "📋 2. Verificando archivos staged..."
if ! git diff --cached --name-only | grep -qE "\.env$"; then
    echo -e "${GREEN}✅ Sin archivos .env en staged${NC}"
else
    echo -e "${RED}❌ HAY archivo .env en staged - NO HAGAS COMMIT${NC}"
    ((ERRORS++))
fi

# 3. Verificar que .env.example no tiene credenciales reales
echo ""
echo "📋 3. Verificando .env.example..."
if ! grep -qE "(Sq7HjrUOBfKmC576ILgskD5srU870gJ7|999008881)" .env.example 2>/dev/null; then
    echo -e "${GREEN}✅ .env.example no contiene credenciales reales${NC}"
else
    echo -e "${RED}❌ .env.example contiene credenciales reales${NC}"
    ((ERRORS++))
fi

# 4. Verificar que config usa env()
echo ""
echo "📋 4. Verificando archivos de configuración..."
if grep -q "env('REDSYS_KEY')" config/redsys.php 2>/dev/null; then
    echo -e "${GREEN}✅ config/redsys.php usa env() correctamente${NC}"
else
    echo -e "${YELLOW}⚠️  No se pudo verificar config/redsys.php${NC}"
    ((WARNINGS++))
fi

# 5. Buscar claves hardcodeadas en PHP
echo ""
echo "📋 5. Buscando claves hardcodeadas..."
if ! git grep -iE "'(merchant_key|api_key|secret)'.*=>.*'[a-zA-Z0-9]{20,}'" -- '*.php' ':(exclude)vendor/' 2>/dev/null; then
    echo -e "${GREEN}✅ Sin claves hardcodeadas detectadas${NC}"
else
    echo -e "${RED}❌ Posibles claves hardcodeadas encontradas${NC}"
    ((ERRORS++))
fi

# 6. Verificar archivos auth.json
echo ""
echo "📋 6. Verificando archivos de autenticación..."
if git ls-files | grep -qE "(^auth\.json$|^\.auth\.json$)"; then
    echo -e "${RED}❌ auth.json está en el repositorio${NC}"
    ((ERRORS++))
else
    echo -e "${GREEN}✅ Sin archivos auth.json en el repositorio${NC}"
fi

# 7. Buscar patrones de credenciales en diff cached
echo ""
echo "📋 7. Verificando cambios staged..."
if git diff --cached | grep -qiE "(merchant.*key.*=.*['\"][a-zA-Z0-9]{20,}|api.*key.*=.*['\"][a-zA-Z0-9]{20,})"; then
    echo -e "${RED}❌ Posibles credenciales en cambios staged${NC}"
    echo "   Ejecuta: git diff --cached | grep -iE \"(merchant|api).*key\""
    ((ERRORS++))
else
    echo -e "${GREEN}✅ Sin credenciales en cambios staged${NC}"
fi

# 8. Verificar que .env está siendo ignorado correctamente
echo ""
echo "📋 8. Verificando estado de .env..."
if git check-ignore -q .env; then
    echo -e "${GREEN}✅ .env está correctamente ignorado${NC}"
else
    echo -e "${YELLOW}⚠️  .env no está siendo ignorado correctamente${NC}"
    ((WARNINGS++))
fi

# 9. Buscar archivos .key o .secret
echo ""
echo "📋 9. Buscando archivos sensibles..."
SENSITIVE_FILES=$(git ls-files | grep -E "\.(key|secret)$" | grep -v ".example" || true)
if [ -z "$SENSITIVE_FILES" ]; then
    echo -e "${GREEN}✅ Sin archivos .key o .secret detectados${NC}"
else
    echo -e "${RED}❌ Archivos sensibles encontrados:${NC}"
    echo "$SENSITIVE_FILES"
    ((ERRORS++))
fi

# 10. Verificar documentación de seguridad existe
echo ""
echo "📋 10. Verificando documentación de seguridad..."
if [ -f "SECURITY_ACTION_REQUIRED.md" ] && [ -f "docs/SECURITY_VERIFICATION_CHECKLIST.md" ]; then
    echo -e "${GREEN}✅ Documentación de seguridad presente${NC}"
else
    echo -e "${YELLOW}⚠️  Falta documentación de seguridad${NC}"
    ((WARNINGS++))
fi

# Resumen
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "📊 RESUMEN DE VERIFICACIÓN"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

if [ $ERRORS -eq 0 ] && [ $WARNINGS -eq 0 ]; then
    echo -e "${GREEN}✅ REPOSITORIO SEGURO${NC}"
    echo "   Todo está correcto. Puedes hacer commit/push con seguridad."
    exit 0
elif [ $ERRORS -eq 0 ]; then
    echo -e "${YELLOW}⚠️  REPOSITORIO CON ADVERTENCIAS (${WARNINGS})${NC}"
    echo "   Revisa las advertencias pero puedes continuar."
    exit 0
else
    echo -e "${RED}❌ REPOSITORIO CON ERRORES (${ERRORS})${NC}"
    echo -e "${RED}   NO hagas commit/push hasta resolver estos errores.${NC}"
    exit 1
fi

