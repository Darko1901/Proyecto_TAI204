<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - MACUIN</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
    <header class="dashboard-header">
        <div class="header-left">
            <h1>MACUIN</h1>
        </div>
        <nav class="top-nav">
            <a href="{{ route('home') }}" class="nav-item"><i class="fas fa-home"></i> Inicio</a>
            <a href="{{ route('dashboard') }}" class="nav-item"><i class="fas fa-th-large"></i> Panel</a>
            <a href="{{ route('catalogo') }}" class="nav-item"><i class="fas fa-store"></i> Catálogo</a>
            <a href="{{ route('pedidos') }}" class="nav-item"><i class="fas fa-box"></i> Pedidos</a>
            <a href="{{ route('perfil') }}" class="nav-item"><i class="fas fa-user-circle"></i> Mi Perfil</a>
            <a href="{{ route('carrito') }}" class="nav-item active cart-icon"><i class="fas fa-shopping-cart"></i></a>
            <form action="{{ route('logout') }}" method="POST" style="display:inline; margin-left:10px;">
                @csrf
                <button type="submit" class="nav-item logout-btn" style="background:rgba(255,255,255,0.1); border:none; cursor:pointer; padding:8px 15px; border-radius:8px; color:white;">Salir</button>
            </form>
        </nav>
    </header>
    
    <main class="dashboard-content">
        <div style="max-width:1200px; margin:0 auto; margin-bottom:20px;">
            <a href="{{ route('carrito') }}" style="text-decoration:none; color:#64748b; font-weight:700; display:flex; align-items:center; gap:8px; transition:0.3s; width:fit-content;" onmouseover="this.style.color='#b71c1c'" onmouseout="this.style.color='#64748b'">
                <i class="fas fa-arrow-left"></i> REGRESAR AL CARRITO
            </a>
        </div>

        <div class="checkout-grid" style="display:grid; grid-template-columns:1.5fr 1fr; gap:40px; max-width:1200px; margin:0 auto;">
            <div class="card" style="padding:40px; border-radius:15px;">
                <div style="display:flex; align-items:center; gap:15px; margin-bottom:30px; border-bottom:2px solid #f0f0f0; padding-bottom:20px;">
                    <div style="width:50px; height:50px; background:#ffebee; border-radius:12px; display:flex; align-items:center; justify-content:center; color:#b71c1c;">
                        <i class="fas fa-truck-fast fa-lg"></i>
                    </div>
                    <div>
                        <h3 style="font-size:1.4rem; font-weight:900; color:#333; margin:0;">Información de Entrega</h3>
                        <p style="font-size:0.85rem; color:#666; margin:0;">Completa los detalles para recibir tu paquete</p>
                    </div>
                </div>

                <form id="checkout-form" method="POST" action="{{ route('pedido.crear') }}">
                    @csrf
                    <input type="hidden" name="carrito_data" id="carrito-data-input">
                    <input type="hidden" name="total" id="total-hidden-input">

                    @if($errors->any())
                        <div style="background:#fff5f5; border:1px solid #feb2b2; color:#c53030; padding:15px; border-radius:10px; margin-bottom:25px; display:flex; align-items:center; gap:10px;">
                            <i class="fas fa-exclamation-circle"></i>
                            <ul style="margin:0; padding:0; list-style:none; font-size:0.9rem; font-weight:600;">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="form-group" style="margin-bottom:25px;">
                        <label style="font-weight:700; color:#444; margin-bottom:10px;"><i class="fas fa-map-marker-alt" style="color:#b71c1c; margin-right:8px;"></i> DIRECCIÓN COMPLETA</label>
                        <input type="text" id="f-direccion" name="direccion" placeholder="Calle, Número, Colonia" value="{{ old('direccion', $usuario['direccion'] ?? '') }}" style="padding:15px; border-radius:10px; border:1px solid {{ $errors->has('direccion') ? '#e53935' : '#ddd' }}; width:100%;">
                        <span class="field-error" id="err-direccion" @if($errors->has('direccion')) style="display:block" @endif>{{ $errors->first('direccion') }}</span>
                    </div>

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:25px;">
                        <div class="form-group">
                            <label style="font-weight:700; color:#444; margin-bottom:10px;"><i class="fas fa-city" style="color:#b71c1c; margin-right:8px;"></i> CIUDAD</label>
                            <input type="text" id="f-ciudad" name="ciudad" placeholder="Ciudad / Estado" value="{{ old('ciudad') }}" style="padding:15px; border-radius:10px; border:1px solid {{ $errors->has('ciudad') ? '#e53935' : '#ddd' }}; width:100%;">
                            <span class="field-error" id="err-ciudad" @if($errors->has('ciudad')) style="display:block" @endif>{{ $errors->first('ciudad') }}</span>
                        </div>
                        <div class="form-group">
                            <label style="font-weight:700; color:#444; margin-bottom:10px;"><i class="fas fa-mail-bulk" style="color:#b71c1c; margin-right:8px;"></i> CÓDIGO POSTAL</label>
                            <input type="text" id="f-cp" name="codigo_postal" placeholder="C.P. (5 dígitos)" maxlength="5" value="{{ old('codigo_postal') }}" style="padding:15px; border-radius:10px; border:1px solid {{ $errors->has('codigo_postal') ? '#e53935' : '#ddd' }}; width:100%;">
                            <span class="field-error" id="err-cp" @if($errors->has('codigo_postal')) style="display:block" @endif>{{ $errors->first('codigo_postal') }}</span>
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom:25px;">
                        <label style="font-weight:700; color:#444; margin-bottom:10px;"><i class="fas fa-phone" style="color:#b71c1c; margin-right:8px;"></i> TELÉFONO DE CONTACTO</label>
                        <input type="tel" id="f-tel" name="telefono_contacto" placeholder="10 dígitos, sin espacios ni guiones" maxlength="10" value="{{ old('telefono_contacto', $usuario['telefono'] ?? '') }}" style="padding:15px; border-radius:10px; border:1px solid {{ $errors->has('telefono_contacto') ? '#e53935' : '#ddd' }}; width:100%;">
                        <span class="field-error" id="err-tel" @if($errors->has('telefono_contacto')) style="display:block" @endif>{{ $errors->first('telefono_contacto') }}</span>
                    </div>

                    <div class="form-group" style="margin-bottom:35px;">
                        <label style="font-weight:700; color:#444; margin-bottom:10px;"><i class="fas fa-info-circle" style="color:#b71c1c; margin-right:8px;"></i> REFERENCIAS DE UBICACIÓN</label>
                        <textarea name="referencias" rows="3" placeholder="Descripción de la fachada, entre qué calles, etc." style="padding:15px; border-radius:10px; border:1px solid #ddd; width:100%; border-radius:10px; resize:none;"></textarea>
                    </div>

                    <div class="payment-section" style="background:#f8f9fa; padding:30px; border-radius:15px; margin-bottom:30px;">
                        <label style="font-weight:900; color:#333; display:block; margin-bottom:20px; text-transform:uppercase; letter-spacing:1px; font-size:0.9rem;">Selecciona tu Método de Pago</label>
                        
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:25px;">
                            <label class="pay-method">
                                <input type="radio" name="metodo_pago" value="Tarjeta" checked onclick="togglePayment('card')"> 
                                <div class="pay-box">
                                    <i class="fas fa-credit-card"></i>
                                    <span>TARJETA</span>
                                </div>
                            </label>
                            <label class="pay-method">
                                <input type="radio" name="metodo_pago" value="Efectivo" onclick="togglePayment('cash')"> 
                                <div class="pay-box">
                                    <i class="fas fa-barcode"></i>
                                    <span>EFECTIVO / TICKET</span>
                                </div>
                            </label>
                        </div>

                        <!-- SECCIÓN TARJETA -->
                        <div id="card-details" style="display:block;">
                            <div class="form-group" style="margin-bottom:15px;">
                                <label style="font-size:0.75rem; font-weight:700; color:#666;">NÚMERO DE TARJETA</label>
                                <input type="text" id="f-card-num" placeholder="0000 0000 0000 0000" maxlength="19" style="padding:12px; border-radius:8px; border:1px solid #ddd; width:100%;">
                                <span class="field-error" id="err-card-num"></span>
                            </div>
                            <div style="display:grid; grid-template-columns:1.5fr 1fr; gap:15px;">
                                <div>
                                    <label style="font-size:0.75rem; font-weight:700; color:#666;">VENCIMIENTO</label>
                                    <input type="text" id="f-card-exp" placeholder="MM/YY" maxlength="5" style="padding:12px; border-radius:8px; border:1px solid #ddd; width:100%;">
                                    <span class="field-error" id="err-card-exp"></span>
                                </div>
                                <div>
                                    <label style="font-size:0.75rem; font-weight:700; color:#666;">CVV</label>
                                    <input type="password" id="f-card-cvv" placeholder="***" maxlength="4" style="padding:12px; border-radius:8px; border:1px solid #ddd; width:100%;">
                                    <span class="field-error" id="err-card-cvv"></span>
                                </div>
                            </div>
                        </div>

                        <!-- SECCIÓN EFECTIVO (CÓDIGO DE BARRAS) -->
                        <div id="cash-details" style="display:none; text-align:center; padding:20px; background:#fff; border-radius:12px; border:1px dashed #ccc;">
                            <p style="font-size:0.85rem; color:#666; margin-bottom:15px;">Presenta este código en cualquier establecimiento autorizado para realizar tu pago.</p>
                            <div style="background:#000; height:80px; width:100%; position:relative; overflow:hidden; border-radius:4px;">
                                <div style="display:flex; height:100%; gap:2px; padding:0 10px;">
                                    @for($i=0; $i<40; $i++)
                                        <div style="background:#fff; width:{{ rand(1, 4) }}px; height:100%;"></div>
                                        <div style="background:#000; width:{{ rand(1, 4) }}px; height:100%;"></div>
                                    @endfor
                                </div>
                            </div>
                            <strong style="display:block; margin-top:10px; font-family:monospace; font-size:1.1rem; letter-spacing:3px;">7890-4561-2234-9102</strong>
                        </div>
                    </div>

                    <div style="background:#e3f2fd; border-radius:12px; padding:20px; display:flex; gap:15px; margin-bottom:30px;">
                        <i class="fas fa-bell" style="color:#1976d2; margin-top:3px;"></i>
                        <p style="font-size:0.9rem; color:#0d47a1; margin:0; line-height:1.4;">
                            <strong>Aviso Importante:</strong> Recibirás una notificación en el panel principal cuando se esté preparando tu pedido.
                        </p>
                    </div>

                    <button type="submit" class="btn-login" style="width:100%; height:60px; font-weight:900; letter-spacing:1px; border-radius:15px; font-size:1.2rem; background:linear-gradient(135deg, #e53935 0%, #b71c1c 100%); border:none; box-shadow: 0 4px 15px rgba(183, 28, 28, 0.3);">
                        CONFIRMAR Y PAGAR ORDEN
                    </button>
                </form>
            </div>

            <div class="card" style="padding:30px; height:fit-content; background:#fff; border-radius:15px; border:1px solid #eee; position:sticky; top:110px;">
                <h3 style="font-size:1.2rem; font-weight:800; margin-bottom:25px; color:#333; display:flex; align-items:center; gap:10px;">
                    <i class="fas fa-shopping-bag" style="color:#b71c1c;"></i> Resumen de Compra
                </h3>
                <div id="checkout-items" style="margin-bottom:25px; border-bottom:1px solid #f0f0f0; padding-bottom:20px;"></div>
                
                <div style="display:flex; justify-content:space-between; margin-bottom:15px; color:#666;">
                    <span>Subtotal:</span> <span id="summary-sub" style="font-weight:600; color:#333;">$0</span>
                </div>
                <div style="display:flex; justify-content:space-between; margin-bottom:15px; color:#1b5e20;">
                    <span style="font-weight:600;">Envío:</span> <span style="font-weight:700;">GRATIS</span>
                </div>
                
                <div style="display:flex; justify-content:space-between; padding-top:20px; border-top:2px solid #b71c1c;">
                    <span style="font-size:1.3rem; font-weight:900; color:#333;">Total Final:</span>
                    <span id="summary-total" style="font-size:1.7rem; font-weight:900; color:#b71c1c;">$0</span>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function togglePayment(type) {
            document.getElementById('card-details').style.display = type === 'card' ? 'block' : 'none';
            document.getElementById('cash-details').style.display = type === 'cash' ? 'block' : 'none';
        }

        // Auto-format card number as user types (groups of 4)
        document.getElementById('f-card-num').addEventListener('input', function() {
            let v = this.value.replace(/\D/g, '').slice(0, 16);
            this.value = v.replace(/(.{4})/g, '$1 ').trim();
        });

        // Auto-format expiry MM/YY
        document.getElementById('f-card-exp').addEventListener('input', function() {
            let v = this.value.replace(/\D/g, '').slice(0, 4);
            if (v.length >= 3) v = v.slice(0, 2) + '/' + v.slice(2);
            this.value = v;
        });

        // Phone: only allow digits
        document.getElementById('f-tel').addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '').slice(0, 10);
        });

        // CP: only allow digits
        document.getElementById('f-cp').addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '').slice(0, 5);
        });

        function setFieldError(fieldId, errorId, message) {
            const field = document.getElementById(fieldId);
            const err   = document.getElementById(errorId);
            if (message) {
                field.style.borderColor = '#e53935';
                field.style.boxShadow = '0 0 0 3px rgba(229,57,53,0.15)';
                err.textContent = message;
                err.style.display = 'block';
                return false;
            } else {
                field.style.borderColor = '#ddd';
                field.style.boxShadow = '';
                err.textContent = '';
                err.style.display = 'none';
                return true;
            }
        }

        function validateCheckout() {
            let ok = true;

            // Dirección
            const dir = document.getElementById('f-direccion').value.trim();
            ok = setFieldError('f-direccion', 'err-direccion', dir ? '' : 'La dirección es obligatoria.') && ok;

            // Ciudad
            const ciudad = document.getElementById('f-ciudad').value.trim();
            ok = setFieldError('f-ciudad', 'err-ciudad', ciudad ? '' : 'La ciudad es obligatoria.') && ok;

            // Código postal: exactamente 5 dígitos
            const cp = document.getElementById('f-cp').value.trim();
            let cpErr = '';
            if (!cp) cpErr = 'El código postal es obligatorio.';
            else if (!/^\d{5}$/.test(cp)) cpErr = 'El código postal debe tener exactamente 5 dígitos.';
            ok = setFieldError('f-cp', 'err-cp', cpErr) && ok;

            // Teléfono: exactamente 10 dígitos
            const tel = document.getElementById('f-tel').value.trim();
            let telErr = '';
            if (!tel) telErr = 'El teléfono es obligatorio.';
            else if (!/^\d{10}$/.test(tel)) telErr = 'El teléfono debe tener exactamente 10 dígitos numéricos.';
            ok = setFieldError('f-tel', 'err-tel', telErr) && ok;

            // Si el pago es con tarjeta, validar campos de tarjeta
            const metodoPago = document.querySelector('input[name="metodo_pago"]:checked');
            if (metodoPago && metodoPago.value === 'Tarjeta') {
                const cardNum = document.getElementById('f-card-num').value.replace(/\s/g, '');
                let cardNumErr = '';
                if (!cardNum) cardNumErr = 'El número de tarjeta es obligatorio.';
                else if (!/^\d{16}$/.test(cardNum)) cardNumErr = 'El número de tarjeta debe tener 16 dígitos.';
                ok = setFieldError('f-card-num', 'err-card-num', cardNumErr) && ok;

                const exp = document.getElementById('f-card-exp').value.trim();
                let expErr = '';
                if (!exp) expErr = 'La fecha de vencimiento es obligatoria.';
                else if (!/^(0[1-9]|1[0-2])\/\d{2}$/.test(exp)) expErr = 'Formato inválido. Usa MM/YY.';
                ok = setFieldError('f-card-exp', 'err-card-exp', expErr) && ok;

                const cvv = document.getElementById('f-card-cvv').value.trim();
                let cvvErr = '';
                if (!cvv) cvvErr = 'El CVV es obligatorio.';
                else if (!/^\d{3,4}$/.test(cvv)) cvvErr = 'El CVV debe tener 3 o 4 dígitos.';
                ok = setFieldError('f-card-cvv', 'err-card-cvv', cvvErr) && ok;
            }

            return ok;
        }

        // Interceptar envío del formulario para validar y mostrar loader
        document.getElementById('checkout-form').addEventListener('submit', function(e) {
            e.preventDefault();
            if (!validateCheckout()) {
                // Scroll al primer error visible
                const firstErr = document.querySelector('.field-error[style*="block"]');
                if (firstErr) firstErr.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }
            Swal.fire({
                title: 'Procesando Pedido',
                text: 'Estamos validando tus datos, por favor espera...',
                allowOutsideClick: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading();
                }
            });
            this.submit();
        });

        function initCheckout() {
            const car = JSON.parse(localStorage.getItem('macuin_carrito') || '[]');
            if(!car.length) { window.location.href = "{{ route('carrito') }}"; return; }
            document.getElementById('carrito-data-input').value = JSON.stringify(car);
            const list = document.getElementById('checkout-items');
            let total = 0;
            car.forEach(x => {
                const s = x.precioNum * x.cantidad;
                total += s;
                list.innerHTML += `
                    <div style="display:flex; gap:15px; align-items:center; margin-bottom:20px; padding-bottom:15px; border-bottom:1px solid #f8f9fa;">
                        <div style="width:60px; height:60px; background:#f9f9f9; border-radius:8px; overflow:hidden; flex-shrink:0; border:1px solid #eee;">
                            <img src="${x.imagen}" style="width:100%; height:100%; object-fit:contain;">
                        </div>
                        <div style="flex:1;">
                            <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                                <span style="font-size:0.9rem; font-weight:700; color:#333; line-height:1.2;">${x.nombre}</span>
                                <strong style="color:#b71c1c; font-size:0.95rem;">$${s.toLocaleString()}</strong>
                            </div>
                            <div style="font-size:0.75rem; color:#999; margin-top:4px;">
                                ${x.marca} | Cantidad: ${x.cantidad}
                            </div>
                        </div>
                    </div>`;
            });
            document.getElementById('summary-sub').textContent = '$'+total.toLocaleString();
            document.getElementById('summary-total').textContent = '$'+total.toLocaleString() + ' MXN';
            document.getElementById('total-hidden-input').value = total;
        }
        initCheckout();
    </script>
    <style>
        .pay-method input { display:none; }
        .pay-box { 
            padding:20px; 
            border:2px solid #e2e8f0; 
            border-radius:12px; 
            text-align:center; 
            font-weight:800; 
            cursor:pointer; 
            color:#64748b; 
            display:flex; 
            flex-direction:column; 
            gap:10px;
            transition: all 0.2s ease;
            background: white;
        }
        .pay-box i { font-size:1.5rem; }
        .pay-method input:checked + .pay-box { 
            border-color:#b71c1c; 
            color:#b71c1c; 
            background:#fff5f5; 
            box-shadow: 0 4px 12px rgba(183, 28, 28, 0.1);
        }
        .form-group input:focus, .form-group textarea:focus {
            outline: none;
            border-color: #b71c1c !important;
            box-shadow: 0 0 0 3px rgba(183, 28, 28, 0.1);
        }
        .field-error {
            display: none;
            color: #e53935;
            font-size: 0.78rem;
            font-weight: 600;
            margin-top: 6px;
        }
    </style>
</body>
</html>
