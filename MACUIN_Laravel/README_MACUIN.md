# MACUIN Laravel - Cliente Web 1 (Clientes Externos)

## Descripción
Este proyecto Laravel maneja toda la funcionalidad relacionada con **clientes externos** de Autopartes MACUIN.

## Estructura del Proyecto

### Vistas Disponibles (`resources/views/`)
- `login.blade.php` - Inicio de sesión para clientes
- `registro.blade.php` - Registro de nuevos clientes
- `dashboard.blade.php` - Panel principal de clientes
- `catalogo.blade.php` - Catálogo de productos
- `detalle_producto.blade.php` - Detalle de un producto específico
- `carrito.blade.php` - Carrito de compras
- `perfil.blade.php` - Perfil de usuario con actualización de datos y contraseña

### Recursos Estáticos (`public/`)
- `css/style.css` - Estilos del proyecto
- `js/script.js` - JavaScript (incluye toggle de contraseña con Font Awesome)
- `img/` - Imágenes del proyecto (frenos.png, amortiguador.png)

### Rutas (`routes/web.php`)
Todas las rutas están configuradas con closures temporales. La lógica debe implementarse en controladores cuando sea necesario.

**Rutas disponibles:**
- `GET /` - Redirección al login
- `GET /login` - Vista de login
- `POST /login` - Procesamiento de login
- `GET /registro` - Vista de registro
- `POST /registro` - Procesamiento de registro
- `GET /dashboard` - Panel de control
- `GET /catalogo` - Catálogo de productos
- `GET /carrito` - Carrito de compras
- `GET /detalle-producto/{id}` - Detalle de producto
- `GET /perfil` - Perfil de usuario
- `POST /perfil/actualizar` - Actualizar datos de perfil
- `POST /perfil/cambiar-password` - Cambiar contraseña

## Instalación

1. Asegúrate de tener las dependencias de Composer instaladas (ya hecho en la creación inicial)

2. Para correr el servidor de desarrollo:
```bash
cd MACUIN_Laravel
php artisan serve
```

3. El servidor estará disponible en `http://localhost:8000`

## Pendiente de Implementación

- [ ] Controladores para cada funcionalidad
- [ ] Modelos de base de datos (User, Product, Cart, etc.)
- [ ] Migraciones de base de datos
- [ ] Sistema de autenticación completo
- [ ] Lógica de carrito de compras
- [ ] Sistema de catálogo con base de datos
- [ ] Integración con el backend de Flask (Personal Interno)

## Notas Importantes

- Este proyecto usa **Font Awesome 6.4.0** para los iconos
- El toggle de contraseña funciona con las clases `fa-eye-slash` y `fa-eye`
- Los archivos de Flask relacionados con clientes externos han sido comentados
- La lógica de negocio debe ser implementada cuando se conecte con base de datos

## Relación con Flask

Este proyecto Laravel complementa al proyecto Flask (`MACUIN_flask`), que maneja:
- Login de personal interno
- Panel administrativo (futuro)
- Gestión de productos (futuro)

Ambos proyectos eventualmente deberán compartir la misma base de datos o comunicarse mediante API.
