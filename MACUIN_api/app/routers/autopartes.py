from fastapi import APIRouter, HTTPException, status, Depends, Form, File, UploadFile
import shutil
import os
from sqlalchemy.orm import Session
from typing import List, Optional
from app.data.database import get_db
from app.models.producto import Producto, ProductoCreate, ProductoUpdate, ProductoResponse
from app.models.inventario import Inventario, InventarioCreate, InventarioUpdate, InventarioResponse
from app.models.categoria import Categoria, CategoriaCreate, CategoriaResponse
from app.security.auth import verificar_token
from app.models.usuario import Usuario
from app.models.resena import Resena
from sqlalchemy import func

router = APIRouter(prefix="/v1/autopartes", tags=["Autopartes"])


# --- Categorías ---

@router.get("/categorias", response_model=List[CategoriaResponse])
async def obtener_categorias(db: Session = Depends(get_db)):
    return db.query(Categoria).all()

@router.post("/categorias", response_model=CategoriaResponse, status_code=status.HTTP_201_CREATED)
async def crear_categoria(datos: CategoriaCreate, db: Session = Depends(get_db), usuario_actual: Usuario = Depends(verificar_token)):
    nueva = Categoria(nombre_categoria=datos.nombre_categoria)
    db.add(nueva)
    db.commit()
    db.refresh(nueva)
    return nueva


# --- Productos ---

@router.get("/")
async def obtener_autopartes(
    categoria: Optional[int] = None, 
    q: Optional[str] = None,
    sort: Optional[str] = None,
    page: int = 1, 
    limit: int = 12, 
    db: Session = Depends(get_db)
):
    """Catálogo con paginación, filtros y ordenamiento."""
    query = db.query(Producto).filter(Producto.activo == 1)
    
    if categoria:
        query = query.filter(Producto.id_categoria == categoria)
    
    if q:
        search = f"%{q}%"
        query = query.filter(
            (Producto.nombre_producto.ilike(search)) | 
            (Producto.descripcion.ilike(search)) |
            (Producto.marca.ilike(search))
        )
    
    if sort == "price_asc":
        query = query.order_by(Producto.precio.asc())
    elif sort == "price_desc":
        query = query.order_by(Producto.precio.desc())
    else:
        query = query.order_by(Producto.id_producto.desc())
    
    total = query.count()
    skip = (page - 1) * limit
    productos = query.offset(skip).limit(limit).all()
    
    # Calcular stock para cada producto
    result = []
    for p in productos:
        total_qty = sum(inv.cantidad for inv in p.inventarios)
        min_qty = max((inv.stock_minimo for inv in p.inventarios), default=0)
        
        # Calcular reseñas
        resena_stats = db.query(
            func.avg(Resena.calificacion).label('avg_rating'),
            func.count(Resena.id_resena).label('review_count')
        ).filter(Resena.id_producto == p.id_producto).first()
        
        # Convertir a dict para añadir campos dinámicos
        prod_data = {
            "id_producto": p.id_producto,
            "nombre_producto": p.nombre_producto,
            "precio": p.precio,
            "id_categoria": p.id_categoria,
            "descripcion": p.descripcion,
            "marca": p.marca,
            "modelo": p.modelo,
            "compatibilidad": p.compatibilidad,
            "garantia": p.garantia,
            "imagen": p.imagen,
            "stock": total_qty,
            "stock_minimo": min_qty,
            "avg_rating": float(resena_stats.avg_rating) if resena_stats.avg_rating else 0,
            "review_count": resena_stats.review_count or 0
        }
        result.append(prod_data)
        
    return {
        "items": result,
        "total": total,
        "page": page,
        "limit": limit,
        "total_pages": (total + limit - 1) // limit
    }


@router.get("/{id_producto}")
async def obtener_autoparte(id_producto: int, db: Session = Depends(get_db)):
    """Detalle de producto — accesible sin token."""
    p = db.query(Producto).filter(Producto.id_producto == id_producto).first()
    if not p:
        raise HTTPException(status_code=404, detail="Autoparte no encontrada")
    
    total_qty = sum(inv.cantidad for inv in p.inventarios)
    min_qty = max((inv.stock_minimo for inv in p.inventarios), default=0)
    
    # Calcular reseñas
    resena_stats = db.query(
        func.avg(Resena.calificacion).label('avg_rating'),
        func.count(Resena.id_resena).label('review_count')
    ).filter(Resena.id_producto == p.id_producto).first()

    return {
        "id_producto": p.id_producto,
        "nombre_producto": p.nombre_producto,
        "precio": p.precio,
        "id_categoria": p.id_categoria,
        "descripcion": p.descripcion,
        "marca": p.marca,
        "modelo": p.modelo,
        "compatibilidad": p.compatibilidad,
        "garantia": p.garantia,
        "imagen": p.imagen,
        "stock": total_qty,
        "stock_minimo": min_qty,
        "avg_rating": float(resena_stats.avg_rating) if resena_stats.avg_rating else 0,
        "review_count": resena_stats.review_count or 0
    }

@router.post("/", response_model=ProductoResponse, status_code=status.HTTP_201_CREATED)
async def crear_autoparte(
    nombre_producto: str = Form(...),
    precio: float = Form(...),
    id_categoria: int = Form(...),
    descripcion: Optional[str] = Form(None),
    marca: Optional[str] = Form(None),
    modelo: Optional[str] = Form(None),
    compatibilidad: Optional[str] = Form(None),
    garantia: Optional[str] = Form(None),
    cantidad_inicial: int = Form(0),
    imagen: Optional[UploadFile] = File(None),
    db: Session = Depends(get_db),
    usuario_actual: Usuario = Depends(verificar_token)
):
    # Guardar imagen si existe
    ruta_imagen = None
    if imagen:
        directorio = "app/static/uploads"
        os.makedirs(directorio, exist_ok=True)
        nombre_archivo = f"{int(os.times().elapsed * 1000)}_{imagen.filename}"
        ruta_archivo = os.path.join(directorio, nombre_archivo)
        with open(ruta_archivo, "wb") as buffer:
            shutil.copyfileobj(imagen.file, buffer)
        ruta_imagen = f"/static/uploads/{nombre_archivo}"

    from app.models.almacen import Almacen
    from sqlalchemy.exc import IntegrityError

    try:
        nuevo = Producto(
            nombre_producto=nombre_producto,
            precio=precio,
            id_categoria=id_categoria,
            descripcion=descripcion,
            marca=marca,
            modelo=modelo,
            compatibilidad=compatibilidad,
            garantia=garantia,
            imagen=ruta_imagen,
            activo=1
        )
        db.add(nuevo)
        db.commit()
        db.refresh(nuevo)
        
        # Verificar que existe al menos un almacén (id_almacen=1 es el default)
        almacen = db.query(Almacen).filter(Almacen.id_almacen == 1).first()
        if not almacen:
            # Crear almacén por defecto si no existe
            nuevo_almacen = Almacen(id_almacen=1, nombre_almacen="Almacén Central", ubicacion="Sede Principal")
            db.add(nuevo_almacen)
            db.commit()
        
        # Crear registro inicial de inventario
        inv_inicial = Inventario(id_producto=nuevo.id_producto, id_almacen=1, cantidad=cantidad_inicial, stock_minimo=5)
        db.add(inv_inicial)
        db.commit()
        
        # Adjuntar stock para la respuesta de Pydantic
        setattr(nuevo, 'stock', cantidad_inicial)
        setattr(nuevo, 'stock_minimo', 5)
        
        return nuevo
    except IntegrityError as e:
        db.rollback()
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail=f"Error de integridad: {str(e.orig)}"
        )
    except Exception as e:
        db.rollback()
        raise HTTPException(status_code=500, detail=str(e))

@router.patch("/{id_producto}", response_model=ProductoResponse)
async def actualizar_autoparte(
    id_producto: int, 
    nombre_producto: Optional[str] = Form(None),
    precio: Optional[float] = Form(None),
    id_categoria: Optional[int] = Form(None),
    descripcion: Optional[str] = Form(None),
    marca: Optional[str] = Form(None),
    modelo: Optional[str] = Form(None),
    compatibilidad: Optional[str] = Form(None),
    garantia: Optional[str] = Form(None),
    imagen: Optional[UploadFile] = File(None),
    db: Session = Depends(get_db), 
    usuario_actual: Usuario = Depends(verificar_token)
):
    producto = db.query(Producto).filter(Producto.id_producto == id_producto).first()
    if not producto:
        raise HTTPException(status_code=404, detail="Autoparte no encontrada")
    
    # Procesar campos de texto
    if nombre_producto is not None: producto.nombre_producto = nombre_producto
    if precio is not None: producto.precio = precio
    if id_categoria is not None: producto.id_categoria = id_categoria
    if descripcion is not None: producto.descripcion = descripcion
    if marca is not None: producto.marca = marca
    if modelo is not None: producto.modelo = modelo
    if compatibilidad is not None: producto.compatibilidad = compatibilidad
    if garantia is not None: producto.garantia = garantia

    # Procesar nueva imagen si existe
    if imagen:
        directorio = "app/static/uploads"
        os.makedirs(directorio, exist_ok=True)
        nombre_archivo = f"upd_{int(os.times().elapsed * 1000)}_{imagen.filename}"
        ruta_archivo = os.path.join(directorio, nombre_archivo)
        with open(ruta_archivo, "wb") as buffer:
            shutil.copyfileobj(imagen.file, buffer)
        producto.imagen = f"/static/uploads/{nombre_archivo}"

    db.commit()
    db.refresh(producto)
    
    # Calcular stock para la respuesta
    producto.stock = sum(inv.cantidad for inv in producto.inventarios)
    producto.stock_minimo = max((inv.stock_minimo for inv in producto.inventarios), default=0)
    
    return producto

@router.delete("/{id_producto}", status_code=status.HTTP_204_NO_CONTENT)
async def eliminar_autoparte(id_producto: int, db: Session = Depends(get_db), usuario_actual: Usuario = Depends(verificar_token)):
    producto = db.query(Producto).filter(Producto.id_producto == id_producto).first()
    if not producto:
        raise HTTPException(status_code=404, detail="Autoparte no encontrada")
    
    # Soft Delete: Marcar como inactivo en lugar de borrar físicamente
    producto.activo = 0
    db.commit()


# --- Inventario ---

@router.get("/{id_producto}/inventario", response_model=List[InventarioResponse])
async def obtener_inventario(id_producto: int, db: Session = Depends(get_db)):
    return db.query(Inventario).filter(Inventario.id_producto == id_producto).all()

@router.post("/{id_producto}/inventario", response_model=InventarioResponse, status_code=status.HTTP_201_CREATED)
async def crear_inventario(id_producto: int, datos: InventarioCreate, db: Session = Depends(get_db), usuario_actual: Usuario = Depends(verificar_token)):
    nuevo = Inventario(id_producto=id_producto, id_almacen=datos.id_almacen, cantidad=datos.cantidad, stock_minimo=datos.stock_minimo)
    db.add(nuevo)
    db.commit()
    db.refresh(nuevo)
    return nuevo

@router.patch("/{id_producto}/inventario/{id_inventario}", response_model=InventarioResponse)
async def actualizar_inventario(id_producto: int, id_inventario: int, datos: InventarioUpdate, db: Session = Depends(get_db), usuario_actual: Usuario = Depends(verificar_token)):
    inv = db.query(Inventario).filter(Inventario.id_inventario == id_inventario, Inventario.id_producto == id_producto).first()
    if not inv:
        raise HTTPException(status_code=404, detail="Registro de inventario no encontrado")
    inv.cantidad = datos.cantidad
    inv.stock_minimo = datos.stock_minimo
    db.commit()
    db.refresh(inv)
    return inv

# --- Endpoint de Ajuste Rápido ---

@router.post("/{id_producto}/ajustar_stock")
async def ajustar_stock_rapido(id_producto: int, cantidad: int, db: Session = Depends(get_db), usuario_actual: Usuario = Depends(verificar_token)):
    """Incrementa o decrementa el stock del primer almacén que encuentre para este producto."""
    inv = db.query(Inventario).filter(Inventario.id_producto == id_producto).first()
    if not inv:
        # Si no hay inventario, crear uno
        inv = Inventario(id_producto=id_producto, id_almacen=1, cantidad=0, stock_minimo=5)
        db.add(inv)
        db.commit()
        db.refresh(inv)
        
    inv.cantidad = max(0, inv.cantidad + cantidad)
    db.commit()
    db.refresh(inv)
    return {"id_producto": id_producto, "nueva_cantidad": inv.cantidad, "status": "success"}
