from fastapi import APIRouter, HTTPException, status, Depends
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
    return query.all()

@router.get("/{id_producto}", response_model=ProductoResponse)
async def obtener_autoparte(id_producto: int, db: Session = Depends(get_db)):
    """Detalle de producto — accesible sin token."""
    producto = db.query(Producto).filter(Producto.id_producto == id_producto).first()
    if not producto:
        raise HTTPException(status_code=404, detail="Autoparte no encontrada")
    return producto

@router.post("/", response_model=ProductoResponse, status_code=status.HTTP_201_CREATED)
async def crear_autoparte(datos: ProductoCreate, db: Session = Depends(get_db), usuario_actual: Usuario = Depends(verificar_token)):
    nuevo = Producto(**datos.model_dump())
    db.add(nuevo)
    db.commit()
    db.refresh(nuevo)
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
