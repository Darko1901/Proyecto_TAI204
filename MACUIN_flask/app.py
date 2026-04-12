from flask import Flask
from routers.auth import auth_bp
from routers.views import views_bp

app = Flask(__name__)
app.secret_key = 'macuin_secreto_123'

# ==========================================
# REGISTRO DE ROUTERS (Estilo FastAPI)
# ==========================================
app.register_blueprint(auth_bp)
app.register_blueprint(views_bp)

if __name__ == '__main__':
    app.run(debug=True, host='0.0.0.0', port=5001)