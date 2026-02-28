# Proyecto MACUIN - Sistema de Autopartes

## Arquitectura del Proyecto

Este proyecto está dividido en dos aplicaciones web independientes:

###  Cliente Web 1: MACUIN_Laravel (Clientes Externos)
**Tecnología:** Laravel (PHP)  
**Puerto:** 8000  
**Propósito:** Gestión de clientes externos

**Funcionalidades:**
- [OK] Login y registro de clientes
- [OK] Dashboard de cliente
- [OK] Catálogo de productos
- [OK] Detalle de productos
- [OK] Carrito de compras
- [OK] Perfil de usuario
- [OK] Actualización de datos personales
- [OK] Cambio de contraseña

###  Cliente Web 2: MACUIN_flask (Personal Interno)
**Tecnología:** Flask (Python)  
**Puerto:** 5001  
**Propósito:** Gestión de personal interno y administración

**Funcionalidades:**
- [OK] Login de personal interno
- [PENDIENTE] Panel administrativo (pendiente)
- [PENDIENTE] Gestión de inventario (pendiente)
- [PENDIENTE] Gestión de pedidos (pendiente)

## Estructura de Directorios

```
Proyecto_TAI204/
├── MACUIN_Laravel/          # Cliente Web 1 - Laravel (Clientes Externos)
│   ├── app/
│   ├── resources/
│   │   └── views/
│   │       ├── login.blade.php
│   │       ├── registro.blade.php
│   │       ├── dashboard.blade.php
│   │       ├── catalogo.blade.php
│   │       ├── carrito.blade.php
│   │       ├── detalle_producto.blade.php
│   │       └── perfil.blade.php
│   ├── public/
│   │   ├── css/style.css
│   │   ├── js/script.js
│   │   └── img/
│   ├── routes/web.php
│   └── README_MACUIN.md
│
└── MACUIN_flask/            # Cliente Web 2 - Flask (Personal Interno)
    ├── app.py               # (Rutas de clientes comentadas)
    ├── templates/
    │   └── login_personal.html
    ├── static/
    │   ├── css/style.css
    │   ├── js/script.js
    │   └── img/
    └── README.md
```

## Instalación y Ejecución

### Laravel (Clientes Externos)

```bash
cd MACUIN_Laravel

# Instalar dependencias (ya hecho en creación inicial)
composer install

# Iniciar servidor
php artisan serve

# Acceder en: http://localhost:8000
```

### Flask (Personal Interno)

```bash
cd MACUIN_flask

# Crear entorno virtual
python -m venv venv
source venv/bin/activate  # Linux/Mac
# o
venv\Scripts\activate     # Windows

# Instalar dependencias
pip install -r requirements.txt

# Iniciar servidor
python app.py

# Acceder en: http://localhost:5001
```

## Estado del Proyecto

### [OK] Completado
- Separación de clientes externos e internos en proyectos independientes
- Migración de vistas de clientes externos a Laravel
- Configuración de rutas básicas en Laravel
- Migración de recursos estáticos (CSS, JS, imágenes)
- Implementación de toggle de contraseña con Font Awesome
- Documentación de ambos proyectos
- **Limpieza de archivos**: Eliminados HTML de clientes externos en Flask
- **Estructura profesional**: Solo archivos necesarios en cada proyecto

### [PENDIENTE] Pendiente
- Implementación de controladores en Laravel
- Modelos y migraciones de base de datos
- Sistema de autenticación completo
- Lógica de negocio del carrito de compras
- Catálogo dinámico con base de datos
- Panel administrativo en Flask
- Integración entre Laravel y Flask (API o base de datos compartida)

## Características Técnicas

### Frontend
- **CSS:** Sistema de estilos compartido
- **JavaScript:** Toggle de contraseña con Font Awesome 6.4.0
- **Iconos:** Font Awesome (`fa-eye`, `fa-eye-slash`)

### Backend
- **Laravel:** Blade templates, rutas con closures temporales
- **Flask:** Rutas de clientes externos comentadas para evitar conflictos

## Notas Importantes

[IMPORTANTE] **Las rutas de clientes externos en Flask han sido comentadas** para evitar conflictos. Si necesitas revertir los cambios, busca las secciones comentadas en `MACUIN_flask/app.py`.

[IMPORTANTE] **Los archivos HTML de clientes externos han sido eliminados de Flask**. Las versiones activas están únicamente en `MACUIN_Laravel/resources/views/` como archivos `.blade.php`.

[IMPORTANTE] **Base de datos:** Actualmente ningún proyecto tiene conexión a base de datos. Esto debe configurarse en futuras iteraciones.

## Próximos Pasos Recomendados

1. Configurar base de datos MySQL/PostgreSQL compartida
2. Crear migraciones en Laravel para tablas de usuarios y productos
3. Implementar controladores en Laravel para cada funcionalidad
4. Desarrollar panel administrativo en Flask
5. Crear API REST en Flask para que Laravel consuma datos
6. Implementar sistema de autenticación robusto en ambos proyectos

##  Documentación Disponible

- **INICIO_RAPIDO.md** - Comandos para iniciar los servidores
- **README_PROYECTO.md** - Este archivo (documentación principal)
- **RESUMEN_CAMBIOS.md** - Historial detallado de cambios
- **LIMPIEZA.md** - Archivos eliminados y estructura final
- **MACUIN_Laravel/README_MACUIN.md** - Documentación específica de Laravel
- **MACUIN_flask/README.md** - Documentación específica de Flask

---

**Fecha de reorganización:** 28 de febrero de 2026  
**Versión:** 2.0
