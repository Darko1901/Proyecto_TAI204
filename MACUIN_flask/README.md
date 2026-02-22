# Autopartes MACUIN - Sistema Web

Sistema web desarrollado con Flask para la gestión de autopartes MACUIN.

## Estructura del Proyecto

```
MACUIN_flask/
├── app.py                  # Aplicación principal de Flask
├── templates/              # Plantillas HTML
│   ├── login.html         # Página de inicio de sesión
│   ├── register.html      # Página de registro
│   └── dashboard.html     # Panel de control
├── static/                 # Archivos estáticos
│   ├── css/
│   │   └── style.css      # Estilos CSS
│   └── js/
│       └── script.js      # JavaScript
├── requirements.txt        # Dependencias de Python
└── README.md              # Este archivo
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

La aplicación estará disponible en: http://localhost:5000

## Características

- ✅ Inicio de sesión de clientes
- ✅ Registro de nuevos usuarios
- ✅ Validación de contraseñas
- ✅ Sistema de sesiones
- ✅ Interfaz responsiva
- ✅ Mensajes flash para feedback
- ✅ Toggle de visibilidad de contraseña

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
