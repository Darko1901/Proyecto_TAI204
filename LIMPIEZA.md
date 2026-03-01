# [OK] Limpieza del Proyecto - Completada

## Archivos Eliminados

### Flask (MACUIN_flask)
Se eliminaron todos los archivos HTML de clientes externos:
- [ELIMINADO] `templates/login.html` → Ahora en Laravel como `login.blade.php`
- [ELIMINADO] `templates/registro.html` → Ahora en Laravel como `registro.blade.php`
- [ELIMINADO] `templates/dashboard.html` → Ahora en Laravel como `dashboard.blade.php`
- [ELIMINADO] `templates/catalogo.html` → Ahora en Laravel como `catalogo.blade.php`
- [ELIMINADO] `templates/carrito.html` → Ahora en Laravel como `carrito.blade.php`
- [ELIMINADO] `templates/detalle_producto.html` → Ahora en Laravel como `detalle_producto.blade.php`
- [ELIMINADO] `templates/perfil.html` → Ahora en Laravel como `perfil.blade.php`
- [ELIMINADO] `templates/register.html` (archivo vacío)

### Scripts de Inicio
- [ELIMINADO] `MACUIN_Laravel/start.sh`
- [ELIMINADO] `MACUIN_flask/start.sh`

**Razón:** Se prefiere ejecutar comandos manualmente en la terminal

## Archivos Mantenidos

### Flask (MACUIN_flask/templates/)
- [OK] `login_personal.html` - **Única vista activa** para personal interno

### Laravel (MACUIN_Laravel/resources/views/)
- [OK] `login.blade.php`
- [OK] `registro.blade.php`
- [OK] `dashboard.blade.php`
- [OK] `catalogo.blade.php`
- [OK] `carrito.blade.php`
- [OK] `detalle_producto.blade.php`
- [OK] `perfil.blade.php`

## Estructura Final Limpia

```
Proyecto_TAI204/
├── INICIO_RAPIDO.md           # Guía de comandos de ejecución
├── README_PROYECTO.md         # Documentación principal
├── RESUMEN_CAMBIOS.md         # Historial de cambios
├── LIMPIEZA.md               # Este archivo
│
├── MACUIN_Laravel/            #  Cliente Web 1
│   ├── resources/views/
│   │   ├── login.blade.php
│   │   ├── registro.blade.php
│   │   ├── dashboard.blade.php
│   │   ├── catalogo.blade.php
│   │   ├── carrito.blade.php
│   │   ├── detalle_producto.blade.php
│   │   └── perfil.blade.php
│   └── ...
│
└── MACUIN_flask/             #  Cliente Web 2
    ├── templates/
    │   └── login_personal.html    # - Única vista
    └── ...
```

## Comandos de Ejecución

### Laravel (Puerto 8000)
```bash
cd MACUIN_Laravel
php artisan serve
```

### Flask (Puerto 5001)
```bash
cd MACUIN_flask
python app.py
```

## Verificación de la Limpieza

```bash
# Verificar templates en Flask (debe mostrar solo login_personal.html)
ls MACUIN_flask/templates/

# Verificar vistas en Laravel (debe mostrar 7 archivos .blade.php de clientes)
ls MACUIN_Laravel/resources/views/ | grep -v welcome

# Verificar que no existen scripts de inicio
ls MACUIN_Laravel/start.sh 2>/dev/null || echo "[OK] Script eliminado"
ls MACUIN_flask/start.sh 2>/dev/null || echo "[OK] Script eliminado"
```

## Beneficios de la Limpieza

1. [OK] **Separación clara**: Clientes externos solo en Laravel
2. [OK] **Sin duplicados**: No hay archivos HTML inactivos en Flask
3. [OK] **Estructura profesional**: Cada proyecto con su responsabilidad definida
4. [OK] **Mantenimiento simple**: Control manual de ejecución
5. [OK] **Menos confusión**: No hay archivos "de referencia" que puedan causar errores

---

**Fecha de limpieza:** 28 de febrero de 2026
