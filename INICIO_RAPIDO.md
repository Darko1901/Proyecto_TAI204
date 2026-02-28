# Guía de Inicio Rápido - MACUIN

## Inicio de Servidores

### Laravel - Cliente Web 1 (Puerto 8000)
```bash
cd MACUIN_Laravel
php artisan serve
```
**Acceder en:** http://localhost:8000

### Flask - Cliente Web 2 (Puerto 5001)
```bash
cd MACUIN_flask
python app.py
```
**Acceder en:** http://localhost:5001

## URLs Disponibles

### Laravel (Clientes Externos)
- Login: http://localhost:8000/login
- Registro: http://localhost:8000/registro
- Dashboard: http://localhost:8000/dashboard
- Catálogo: http://localhost:8000/catalogo
- Carrito: http://localhost:8000/carrito
- Perfil: http://localhost:8000/perfil

### Flask (Personal Interno)
- Login Personal: http://localhost:5001/

## Configuración Inicial (Solo primera vez)

### Laravel
Las dependencias ya están instaladas. Si necesitas reinstalarlas:
```bash
cd MACUIN_Laravel
composer install
php artisan key:generate
```

### Flask
```bash
cd MACUIN_flask
python -m venv venv
source venv/bin/activate  # Linux/Mac
# o
venv\Scripts\activate     # Windows
pip install -r requirements.txt
```

## Detener los Servidores

Presiona `Ctrl + C` en la terminal donde está corriendo el servidor.

## Solución de Problemas

### Puerto ocupado
Si ves error "Address already in use":
```bash
# Para Laravel (puerto 8000)
lsof -ti:8000 | xargs kill -9

# Para Flask (puerto 5001)
lsof -ti:5001 | xargs kill -9
```

## Más Información

- **Documentación completa:** Ver `README_PROYECTO.md`
- **Cambios realizados:** Ver `RESUMEN_CAMBIOS.md`
- **Laravel específico:** Ver `MACUIN_Laravel/README_MACUIN.md`
- **Flask específico:** Ver `MACUIN_flask/README.md`

---

**Nota:** Puedes ejecutar ambos servidores simultáneamente en terminales diferentes.
