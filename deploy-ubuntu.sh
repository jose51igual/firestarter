#!/bin/bash

# ===========================================
# Firestarter VPS Deployment Script
# Para Ubuntu Server 20.04/22.04/24.04
# ===========================================

set -e

echo "🔥 Firestarter VPS Deployment Script"
echo "====================================="

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuración - EDITAR ESTOS VALORES
DOMAIN="tu-dominio.com"  # Cambiar por tu dominio o IP
APP_DIR="/var/www/firestarter"
REPO_URL="https://github.com/mendableai/firestarter.git"  # O tu repositorio

# ============================================
# PASO 1: Actualizar sistema
# ============================================
echo -e "${YELLOW}[1/8] Actualizando sistema...${NC}"
sudo apt update && sudo apt upgrade -y

# ============================================
# PASO 2: Instalar Node.js 20 LTS
# ============================================
echo -e "${YELLOW}[2/8] Instalando Node.js 20...${NC}"
if ! command -v node &> /dev/null; then
    curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
    sudo apt install -y nodejs
fi
echo -e "${GREEN}Node.js version: $(node --version)${NC}"

# ============================================
# PASO 3: Instalar herramientas globales
# ============================================
echo -e "${YELLOW}[3/8] Instalando PM2 y herramientas...${NC}"
sudo npm install -g pm2

# ============================================
# PASO 4: Instalar Nginx
# ============================================
echo -e "${YELLOW}[4/8] Instalando Nginx...${NC}"
sudo apt install -y nginx
sudo systemctl enable nginx
sudo systemctl start nginx

# ============================================
# PASO 5: Crear directorio de la aplicación
# ============================================
echo -e "${YELLOW}[5/8] Configurando directorio de la aplicación...${NC}"
sudo mkdir -p $APP_DIR
sudo chown -R $USER:$USER $APP_DIR

# ============================================
# PASO 6: Clonar o copiar el proyecto
# ============================================
echo -e "${YELLOW}[6/8] Preparando aplicación...${NC}"
echo -e "${YELLOW}Ahora debes subir tu código al servidor.${NC}"
echo ""
echo "Opciones:"
echo "  A) Desde tu PC local con SCP:"
echo "     scp -r ./firestarter/* usuario@tu-servidor:$APP_DIR/"
echo ""
echo "  B) Clonar desde GitHub:"
echo "     cd $APP_DIR && git clone $REPO_URL ."
echo ""

# ============================================
# PASO 7: Configurar Nginx
# ============================================
echo -e "${YELLOW}[7/8] Configurando Nginx...${NC}"

sudo tee /etc/nginx/sites-available/firestarter > /dev/null <<EOF
server {
    listen 80;
    server_name $DOMAIN;

    location / {
        proxy_pass http://localhost:3000;
        proxy_http_version 1.1;
        proxy_set_header Upgrade \$http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host \$host;
        proxy_set_header X-Real-IP \$remote_addr;
        proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto \$scheme;
        proxy_cache_bypass \$http_upgrade;
        proxy_read_timeout 86400;
        proxy_send_timeout 86400;
    }
}
EOF

sudo ln -sf /etc/nginx/sites-available/firestarter /etc/nginx/sites-enabled/
sudo rm -f /etc/nginx/sites-enabled/default
sudo nginx -t
sudo systemctl reload nginx

echo -e "${GREEN}Nginx configurado correctamente${NC}"

# ============================================
# PASO 8: Instrucciones finales
# ============================================
echo ""
echo -e "${GREEN}====================================="
echo "✅ Configuración base completada!"
echo "=====================================${NC}"
echo ""
echo "PRÓXIMOS PASOS:"
echo ""
echo "1. Sube tu código al servidor:"
echo "   scp -r ./* usuario@servidor:$APP_DIR/"
echo ""
echo "2. Conecta al servidor y ve al directorio:"
echo "   cd $APP_DIR"
echo ""
echo "3. Crea el archivo .env.local con tus API keys:"
echo "   nano .env.local"
echo ""
echo "4. Instala dependencias y construye:"
echo "   npm install"
echo "   npm run build"
echo ""
echo "5. Inicia con PM2:"
echo "   pm2 start ecosystem.config.js --env production"
echo "   pm2 save"
echo "   pm2 startup"
echo ""
echo "6. (Opcional) Configura SSL con Certbot:"
echo "   sudo apt install certbot python3-certbot-nginx"
echo "   sudo certbot --nginx -d $DOMAIN"
echo ""
echo -e "${GREEN}¡Tu API estará disponible en http://$DOMAIN!${NC}"
