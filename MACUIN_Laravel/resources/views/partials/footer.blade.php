<footer class="footer-premium">
    <div class="footer-container">
        <div class="footer-grid">
            <!-- Brand Section -->
            <div class="footer-brand">
                <h2 class="footer-logo">MACUIN<span>.</span></h2>
                <p class="footer-desc">Soluciones expertas en autopartes de alto rendimiento. Calidad y confianza para mantenerte siempre en movimiento.</p>
                <div class="footer-social">
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-linkedin"></i></a>
                </div>
            </div>

            <!-- Links Sections -->
            <div class="footer-links">
                <h3>Explorar</h3>
                <ul>
                    <li><a href="/catalogo">Catálogo</a></li>
                    <li><a href="#">Promociones</a></li>
                    <li><a href="#">Nuevos Productos</a></li>
                    <li><a href="#">Marcas Aliadas</a></li>
                </ul>
            </div>

            <div class="footer-links">
                <h3>Servicio</h3>
                <ul>
                    <li><a href="/perfil">Mi Cuenta</a></li>
                    <li><a href="#">Rastreo de Pedido</a></li>
                    <li><a href="#">Centro de Ayuda</a></li>
                    <li><a href="#">Términos Legales</a></li>
                </ul>
            </div>

            <!-- Contact Section -->
            <div class="footer-contact">
                <h3>Contacto</h3>
                <div class="contact-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>Querétaro, Qro. México CP. 76000</span>
                </div>
                <div class="contact-item">
                    <i class="fas fa-phone"></i>
                    <span>+52 442 123 4567</span>
                </div>
                <div class="contact-item">
                    <i class="fas fa-envelope"></i>
                    <span>soporte@macuin.com</span>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <div class="bottom-content">
                <p>&copy; 2024 MACUIN S.A. de C.V. Todos los derechos reservados.</p>
                <div class="bottom-links">
                    <span>Protocolo de Seguridad SSL <i class="fas fa-lock"></i></span>
                    <span class="separator">|</span>
                    <span>TAI204 Project</span>
                </div>
            </div>
        </div>
    </div>
</footer>

<style>
    .footer-premium {
        background-color: #1a1a1a;
        color: #f1f5f9;
        font-family: 'Inter', sans-serif;
        padding: 4rem 0 0 0;
        margin-top: 4rem;
        border-top: 4px solid var(--primary);
    }
    .footer-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 1.5rem;
    }
    .footer-grid {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr 1.5fr;
        gap: 3rem;
        padding-bottom: 4rem;
    }
    .footer-logo {
        font-size: 1.75rem;
        font-weight: 800;
        margin-bottom: 1.5rem;
        letter-spacing: -0.05em;
    }
    .footer-logo span { color: var(--primary); }
    .footer-desc {
        color: #94a3b8;
        font-size: 0.9375rem;
        margin-bottom: 2rem;
        line-height: 1.7;
    }
    .footer-social {
        display: flex;
        gap: 1rem;
    }
    .footer-social a {
        width: 2.5rem;
        height: 2.5rem;
        background: rgba(255,255,255,0.05);
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        color: white;
        text-decoration: none;
        transition: var(--transition);
        border: 1px solid rgba(255,255,255,0.1);
    }
    .footer-social a:hover {
        background: var(--primary);
        border-color: var(--primary);
        transform: translateY(-3px);
    }
    .footer-links h3, .footer-contact h3 {
        font-size: 0.875rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        margin-bottom: 1.5rem;
        color: #cbd5e1;
    }
    .footer-links ul {
        list-style: none;
        padding: 0;
    }
    .footer-links li { margin-bottom: 0.75rem; }
    .footer-links a {
        color: #94a3b8;
        text-decoration: none;
        transition: var(--transition);
        font-size: 0.9375rem;
    }
    .footer-links a:hover {
        color: white;
        padding-left: 0.5rem;
    }
    .footer-contact .contact-item {
        display: flex;
        gap: 1rem;
        margin-bottom: 1.25rem;
        color: #94a3b8;
        font-size: 0.9375rem;
    }
    .footer-contact i {
        color: var(--primary);
        width: 1.25rem;
        text-align: center;
    }
    .footer-bottom {
        border-top: 1px solid rgba(255,255,255,0.05);
        padding: 1.5rem 0;
        background: rgba(0,0,0,0.2);
    }
    .bottom-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 1.5rem;
        color: #64748b;
        font-size: 0.8125rem;
    }
    .bottom-links {
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    .bottom-links i { color: #10b981; }
    .separator { opacity: 0.3; }

    @media (max-width: 768px) {
        .footer-grid {
            grid-template-columns: 1fr;
            gap: 2.5rem;
            text-align: center;
        }
        .footer-social { justify-content: center; }
        .footer-contact .contact-item { justify-content: center; }
        .bottom-content {
            flex-direction: column;
            gap: 1rem;
        }
    }
</style>
