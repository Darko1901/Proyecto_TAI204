# Importamos todos los modelos para que SQLAlchemy los registre en Base
# Esto es necesario para que Base.metadata.create_all() cree todas las tablas
from app.models.rol import Rol
from app.models.usuario import Usuario
from app.models.categoria import Categoria
from app.models.producto import Producto
from app.models.almacen import Almacen
from app.models.inventario import Inventario
from app.models.estado_pedido import EstadoPedido
from app.models.detalle_pedido import DetallePedido
from app.models.pedido import Pedido
from app.models.envio import Envio
