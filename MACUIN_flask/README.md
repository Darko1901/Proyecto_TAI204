# Autopartes MACUIN - Sistema Web Flask (Cliente web 2 - Personal Interno)

Sistema web desarrollado con Flask para la gestión de **personal interno** de Autopartes MACUIN.

## [IMPORTANTE] IMPORTANTE - Cambio de Arquitectura

Este proyecto ha sido reorganizado. Ahora maneja **únicamente el sistema para personal interno**.

Las funcionalidades de **clientes externos** (catálogo, carrito, perfil, etc.) han sido migradas al proyecto Laravel ubicado en `../MACUIN_Laravel/`.

### División de Responsabilidades:
- **Flask (este proyecto)**: Personal interno, administración
- **Laravel (MACUIN_Laravel)**: Clientes externos, catálogo, compras

## Estructura del Proyecto

```
MACUIN_flask/
├── app.py                     # Aplicación principal de Flask (código comentado de clientes externos)
├── templates/                 # Plantillas HTML
│   └── login_personal.html   # [OK] Login para personal interno
├── static/                    # Archivos estáticos
│   ├── css/
│   │   └── style.css         # Estilos CSS
│   ├── js/
│   │   └── script.js         # JavaScript con toggle de contraseña
│   └── img/
│       └── *.png             # Imágenes
├── requirements.txt           # Dependencias de Python
└── README.md                 # Este archivo
```

## Instalación

1. Crear un entorno virtual (recomendado):
```bash
python -m venv venv
source venv/bin/activate  # En Linux/Mac
# o
venv\Scripts\activate     # En Windows
```

2. Instalar dependencias:
```bash
pip install -r requirements.txt
```

## Ejecución

Para ejecutar la aplicación:

```bash
python app.py
```

La aplicación estará disponible en: http://localhost:5001

## Rutas Activas (Personal Interno)

- `GET /` - Login de personal interno
- `GET /login_personal_interno` - Login de personal interno

## Rutas Comentadas (Migradas a Laravel)

Las siguientes rutas han sido comentadas en `app.py` ya que ahora están en Laravel:
- `/login` (clientes)
- `/registro`
- `/dashboard`
- `/catalogo`
- `/carrito`
- `/perfil`
- `/producto/<id>`
- `/agregar_carrito/<id>`
- `/eliminar_carrito/<id>`

## Características Activas

- [OK] Inicio de sesión de personal interno
- [OK] Registro de nuevos usuarios
- [OK] Validación de contraseñas
- [OK] Sistema de sesiones
- [OK] Interfaz responsiva
- [OK] Mensajes flash para feedback
- [OK] Toggle de visibilidad de contraseña

## Próximas Mejoras

- [ ] Integración con base de datos real (SQLite/PostgreSQL)
- [ ] Recuperación de contraseña
- [ ] Validación de email
- [ ] Sistema de roles (Admin/Cliente)
- [ ] Catálogo de productos
- [ ] Carrito de compras
- [ ] Sistema de pedidos

## Tecnologías Utilizadas

- **Backend**: Flask (Python)
- **Frontend**: HTML5, CSS3, JavaScript
- **Seguridad**: Werkzeug (hash de contraseñas)
