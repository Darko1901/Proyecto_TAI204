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

@router.get("/", response_model=List[ProductoResponse])
async def obtener_autopartes(categoria: Optional[int] = None, db: Session = Depends(get_db)):
    """Catálogo público — accesible sin token."""
    query = db.query(Producto)
    if categoria:
        query = query.filter(Producto.id_categoria == categoria)
    
    productos = query.all()
    
    # Calcular stock para cada producto
    for p in productos:
        total_qty = sum(inv.cantidad for inv in p.inventarios)
        min_qty = max((inv.stock_minimo for inv in p.inventarios), default=0)
        p.stock = total_qty
        p.stock_minimo = min_qty
        
    return productos

@router.get("/{id_producto}", response_model=ProductoResponse)
async def obtener_autoparte(id_producto: int, db: Session = Depends(get_db)):
    """Detalle de producto — accesible sin token."""
    producto = db.query(Producto).filter(Producto.id_producto == id_producto).first()
    if not producto:
        raise HTTPException(status_code=404, detail="Autoparte no encontrada")
    return producto

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

    nuevo = Producto(
        nombre_producto=nombre_producto,
        precio=precio,
        id_categoria=id_categoria,
        descripcion=descripcion,
        marca=marca,
        modelo=modelo,
        compatibilidad=compatibilidad,
        garantia=garantia,
        imagen=ruta_imagen
    )
    db.add(nuevo)
    db.commit()
    db.refresh(nuevo)
    
    # Crear registro inicial de inventario
    inv_inicial = Inventario(id_producto=nuevo.id_producto, id_almacen=1, cantidad=cantidad_inicial, stock_minimo=5)
    db.add(inv_inicial)
    db.commit()
    
    # Adjuntar stock para la respuesta
    nuevo.stock = cantidad_inicial
    nuevo.stock_minimo = 5
    
    return nuevo

@router.patch("/{id_producto}", response_model=ProductoResponse)
async def actualizar_autoparte(id_producto: int, datos: ProductoUpdate, db: Session = Depends(get_db), usuario_actual: Usuario = Depends(verificar_token)):
    producto = db.query(Producto).filter(Producto.id_producto == id_producto).first()
    if not producto:
        raise HTTPException(status_code=404, detail="Autoparte no encontrada")
    for campo, valor in datos.model_dump(exclude_none=True).items():
        setattr(producto, campo, valor)
    db.commit()
    db.refresh(producto)
    return producto

@router.delete("/{id_producto}", status_code=status.HTTP_204_NO_CONTENT)
async def eliminar_autoparte(id_producto: int, db: Session = Depends(get_db), usuario_actual: Usuario = Depends(verificar_token)):
    producto = db.query(Producto).filter(Producto.id_producto == id_producto).first()
    if not producto:
        raise HTTPException(status_code=404, detail="Autoparte no encontrada")
    db.delete(producto)
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
