# Scripts de Utilidad

Este directorio contiene scripts útiles para el desarrollo y mantenimiento del proyecto.

## 🔐 verify-security.sh

Script de verificación de seguridad del repositorio que comprueba que no hay credenciales sensibles expuestas.

### Uso

```bash
./scripts/verify-security.sh
```

### ¿Qué verifica?

1. ✅ `.env` está en `.gitignore`
2. ✅ No hay archivos `.env` en staged
3. ✅ `.env.example` no contiene credenciales reales
4. ✅ Archivos de configuración usan `env()`
5. ✅ No hay claves hardcodeadas en el código
6. ✅ No hay archivos `auth.json` en el repositorio
7. ✅ No hay credenciales en cambios staged
8. ✅ `.env` está siendo ignorado correctamente
9. ✅ No hay archivos `.key` o `.secret` sin ignorar
10. ✅ Existe la documentación de seguridad

### Cuándo ejecutarlo

- **Antes de hacer commit:** Para asegurar que no commiteas información sensible
- **Antes de hacer push:** Verificación final antes de subir cambios
- **Después de onboarding:** Cuando un nuevo desarrollador se une al equipo
- **Periódicamente:** Como parte de una revisión de seguridad semanal/mensual
- **Antes de un release:** Verificación final antes de publicar una versión

### Salida del script

El script puede devolver tres estados:

#### ✅ REPOSITORIO SEGURO (exit code 0)

Todo está correcto. Puedes hacer commit/push con seguridad.

#### ⚠️ REPOSITORIO CON ADVERTENCIAS (exit code 0)

Hay advertencias menores, revísalas pero puedes continuar.

#### ❌ REPOSITORIO CON ERRORES (exit code 1)

**NO hagas commit/push** hasta resolver los errores críticos.

### Integración con Git Hooks

Puedes configurar este script como pre-commit hook:

```bash
# Opción 1: Enlace simbólico
ln -s ../../scripts/verify-security.sh .git/hooks/pre-commit

# Opción 2: Copiar el script
cp scripts/verify-security.sh .git/hooks/pre-commit
chmod +x .git/hooks/pre-commit
```

### Integración en CI/CD

Ejemplo para GitHub Actions:

```yaml
-   name: Verify Security
    run: ./scripts/verify-security.sh
```

Ejemplo para GitLab CI:

```yaml
security-check:
    script:
        - ./scripts/verify-security.sh
```

### Personalización

Puedes modificar el script para agregar verificaciones específicas de tu proyecto:

```bash
# Agregar nuevas verificaciones al final del script
echo ""
echo "📋 11. Tu nueva verificación..."
if [ condición ]; then
    echo -e "${GREEN}✅ Verificación pasada${NC}"
else
    echo -e "${RED}❌ Verificación falló${NC}"
    ((ERRORS++))
fi
```

## Agregar Nuevos Scripts

Cuando agregues nuevos scripts:

1. **Añade permisos de ejecución:**
   ```bash
   chmod +x scripts/nuevo-script.sh
   ```

2. **Documenta en este README:**
    - Propósito del script
    - Cómo usarlo
    - Cuándo ejecutarlo

3. **Incluye comentarios en el script:**
   ```bash
   #!/bin/bash
   # Descripción breve del script
   # Autor: Tu nombre
   # Fecha: YYYY-MM-DD
   ```

4. **Sigue las convenciones:**
    - Usa bash como shell (#!/bin/bash)
    - Añade colores para mejor UX
    - Devuelve códigos de salida apropiados (0 = éxito, 1 = error)
    - Incluye mensajes claros de error/éxito

## Estructura Recomendada

```
scripts/
├── README.md                    # Este archivo
├── verify-security.sh           # Verificación de seguridad
├── setup/                       # Scripts de configuración
│   └── install-dependencies.sh
├── testing/                     # Scripts de testing
│   └── run-all-tests.sh
└── deployment/                  # Scripts de deployment
    └── deploy-production.sh
```

## Buenas Prácticas

1. **Hacer scripts idempotentes:** Ejecutarlos múltiples veces debe ser seguro
2. **Validar prerrequisitos:** Verificar que existen los archivos/comandos necesarios
3. **Usar colores:** Verde para éxito, rojo para error, amarillo para advertencias
4. **Logging claro:** Mensajes descriptivos de lo que está haciendo
5. **Exit codes:** 0 para éxito, 1+ para errores
6. **Documentar parámetros:** Si el script acepta argumentos, documéntalos

## Ejemplo de Uso en Desarrollo

```bash
# Antes de hacer commit
./scripts/verify-security.sh && git commit -m "feat: nueva funcionalidad"

# Si falla, NO se hará el commit
./scripts/verify-security.sh || echo "Arregla los errores antes de commitear"
```

## Recursos

- [Bash Scripting Guide](https://www.gnu.org/software/bash/manual/)
- [ShellCheck](https://www.shellcheck.net/) - Linter para scripts bash
- [Google Shell Style Guide](https://google.github.io/styleguide/shellguide.html)

