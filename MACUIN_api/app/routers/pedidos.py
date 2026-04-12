from fastapi import APIRouter, HTTPException, status, Depends
from sqlalchemy.orm import Session
from typing import List
from datetime import datetime
from app.data.database import get_db
from app.models.pedido import Pedido, PedidoCreate, PedidoResponse, PedidoDetalladoResponse, PedidoEstadoUpdate
from app.models.detalle_pedido import DetallePedido
from app.models.envio import Envio, EnvioResponse
from app.models.inventario import Inventario
from app.security.auth import verificar_token
from app.models.usuario import Usuario

router = APIRouter(prefix="/v1/pedidos", tags=["Pedidos"])


@router.post("/", response_model=PedidoResponse, status_code=status.HTTP_201_CREATED)
async def crear_pedido(datos: PedidoCreate, db: Session = Depends(get_db), usuario_actual: Usuario = Depends(verificar_token)):
    """Crea un pedido con 1 a N productos. Genera automáticamente el envío."""
    total = sum(d.cantidad * d.precio_unitario for d in datos.detalles)

    nuevo_pedido = Pedido(
        id_usuario=usuario_actual.id_usuario,
        id_estado=1,  # 1 = recibido
        total=total
    )
    db.add(nuevo_pedido)
    db.flush()  # Obtenemos el id_pedido sin hacer commit aún

    for detalle in datos.detalles:
        # 1. Reducir Stock
        inv = db.query(Inventario).filter(Inventario.id_producto == detalle.id_producto).first()
        if not inv or inv.cantidad < detalle.cantidad:
            db.rollback()
            raise HTTPException(
                status_code=400, 
                detail=f"Stock insuficiente para el producto ID {detalle.id_producto}"
            )
        inv.cantidad -= detalle.cantidad

        # 2. Agregar Detalle
        db.add(DetallePedido(
            id_pedido=nuevo_pedido.id_pedido,
            id_producto=detalle.id_producto,
            cantidad=detalle.cantidad,
            precio_unitario=detalle.precio_unitario
        ))

    db.add(Envio(
        id_pedido=nuevo_pedido.id_pedido,
        direccion=datos.direccion,
        ciudad=datos.ciudad,
        codigo_postal=datos.codigo_postal,
        telefono_contacto=datos.telefono_contacto,
        notas=datos.notas
    ))

    db.commit()
    db.refresh(nuevo_pedido)
    return nuevo_pedido


@router.get("/mis-pedidos", response_model=List[PedidoDetalladoResponse])
async def mis_pedidos(db: Session = Depends(get_db), usuario_actual: Usuario = Depends(verificar_token)):
    """Historial de pedidos del usuario autenticado."""
    return db.query(Pedido).filter(Pedido.id_usuario == usuario_actual.id_usuario).all()


@router.get("/", response_model=List[PedidoDetalladoResponse])
async def todos_los_pedidos(db: Session = Depends(get_db), usuario_actual: Usuario = Depends(verificar_token)):
    """Visualización global de pedidos — para personal interno."""
    return db.query(Pedido).all()


@router.get("/{id_pedido}", response_model=PedidoDetalladoResponse)
async def obtener_pedido(id_pedido: int, db: Session = Depends(get_db), usuario_actual: Usuario = Depends(verificar_token)):
    pedido = db.query(Pedido).filter(Pedido.id_pedido == id_pedido).first()
    if not pedido:
        raise HTTPException(status_code=404, detail="Pedido no encontrado")
    return pedido


@router.get("/{id_pedido}/envio", response_model=EnvioResponse)
async def obtener_envio(id_pedido: int, db: Session = Depends(get_db), usuario_actual: Usuario = Depends(verificar_token)):
    envio = db.query(Envio).filter(Envio.id_pedido == id_pedido).first()
    if not envio:
        raise HTTPException(status_code=404, detail="Envío no encontrado")
    return envio


@router.patch("/{id_pedido}/cancelar", response_model=PedidoResponse)
async def cancelar_pedido(id_pedido: int, db: Session = Depends(get_db), usuario_actual: Usuario = Depends(verificar_token)):
    """Solo se pueden cancelar pedidos en estado 'recibido' (id_estado=1)."""
    pedido = db.query(Pedido).filter(
        Pedido.id_pedido == id_pedido,
        Pedido.id_usuario == usuario_actual.id_usuario
    ).first()
    if not pedido:
        raise HTTPException(status_code=404, detail="Pedido no encontrado")
    if pedido.id_estado != 1:
        raise HTTPException(status_code=400, detail="Solo se pueden cancelar pedidos en estado 'recibido'")
    pedido.id_estado = 5  # 5 = cancelado
    db.commit()
    db.refresh(pedido)
    return pedido


@router.patch("/{id_pedido}/estado", response_model=PedidoResponse)
async def cambiar_estado(id_pedido: int, datos: PedidoEstadoUpdate, db: Session = Depends(get_db), usuario_actual: Usuario = Depends(verificar_token)):
    """Cambio de estado para personal interno: recibido(1), surtido(2), enviado(3), entregado(4), cancelado(5)."""
    pedido = db.query(Pedido).filter(Pedido.id_pedido == id_pedido).first()
    if not pedido:
        raise HTTPException(status_code=404, detail="Pedido no encontrado")
    pedido.id_estado = datos.id_estado
    if datos.id_estado == 3:  # enviado → registrar fecha de envío
        envio = db.query(Envio).filter(Envio.id_pedido == id_pedido).first()
        if envio:
            envio.fecha_envio = datetime.utcnow()
            envio.estado_envio = "enviado"
    db.commit()
    db.refresh(pedido)
    return pedido

@router.delete("/{id_pedido}", status_code=status.HTTP_204_NO_CONTENT)
async def eliminar_pedido(id_pedido: int, db: Session = Depends(get_db), usuario_actual: Usuario = Depends(verificar_token)):
    """Elimina un pedido y restaura el stock al inventario."""
    pedido = db.query(Pedido).filter(Pedido.id_pedido == id_pedido).first()
    if not pedido:
        raise HTTPException(status_code=404, detail="Pedido no encontrado")
    
    # 1. Restaurar Stock
    for detalle in pedido.detalles:
        inv = db.query(Inventario).filter(Inventario.id_producto == detalle.id_producto).first()
        if inv:
            inv.cantidad += detalle.cantidad
    
    # 2. Eliminar Envio (si existe)
    if pedido.envio:
        db.delete(pedido.envio)
    
    # 3. Eliminar Detalles
    for d in pedido.detalles:
        db.delete(d)
        
    # 4. Eliminar Pedido
    db.delete(pedido)
    db.commit()
    return None


@router.patch("/{id_pedido}/envio", response_model=EnvioResponse)
async def actualizar_envio(id_pedido: int, datos: dict, db: Session = Depends(get_db), usuario_actual: Usuario = Depends(verificar_token)):
    """Actualiza la dirección y datos de contacto de un pedido."""
    envio = db.query(Envio).filter(Envio.id_pedido == id_pedido).first()
    if not envio:
        raise HTTPException(status_code=404, detail="Envío no encontrado")
    
    for key, value in datos.items():
        if hasattr(envio, key) and value not in [None, ""]:
            setattr(envio, key, value)
            
    db.commit()
    db.refresh(envio)
    return envio


# --- Logística / Envíos ---

@router.get("/envios/todos", response_model=List[EnvioResponse])
async def obtener_todos_los_envios(db: Session = Depends(get_db), usuario_actual: Usuario = Depends(verificar_token)):
    """Retorna todos los registros de envío para el módulo de logística."""
    return db.query(Envio).all()
