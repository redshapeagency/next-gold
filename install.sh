#!/bin/bash

# Next Gold - Automated Installation Script for Ubuntu 24.04
# This script sets up the complete Next Gold application environment
# Version: 2.0 - Complete rewrite for reliability and fixing Redis/Permission issues

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Logging function
log() {
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')] $1${NC}"
}

error() {
    echo -e "${RED}[ERROR] $1${NC}" >&2
}

warning() {
    echo -e "${YELLOW}[WARNING] $1${NC}"
}

info() {
    echo -e "${BLUE}[INFO] $1${NC}"
}

# Check if running as root
if [[ $EUID -eq 0 ]]; then
   error "This script should not be run as root. Please run as a regular user with sudo privileges."
   exit 1
fi

# Check Ubuntu version
if ! lsb_release -d | grep -q "Ubuntu 24.04"; then
    warning "This script is designed for Ubuntu 24.04. Continue anyway? (y/N)"
    read -r response
    if [[ ! $response =~ ^[Yy]$ ]]; then
        exit 1
    fi
fi

# Configuration variables
APP_NAME="next-gold"
APP_DOMAIN=""
DB_NAME="next_gold"
DB_USER="next_gold_user"
DB_PASSWORD=""
REDIS_PASSWORD=""
APP_ENV="production"
SETUP_SSL=false
BACKUP_ENABLED=true

# Get configuration from user
get_configuration() {
    log "Getting configuration..."
    
    while [[ -z "$APP_DOMAIN" ]]; do
        read -p "Enter your domain name (e.g., nextgold.example.com): " APP_DOMAIN
    done
    
    while [[ -z "$DB_PASSWORD" ]]; do
        read -s -p "Enter database password: " DB_PASSWORD
        echo
    done
    
    read -p "Generate Redis password automatically? (Y/n): " redis_auto
    if [[ $redis_auto =~ ^[Nn]$ ]]; then
        while [[ -z "$REDIS_PASSWORD" ]]; do
            read -s -p "Enter Redis password: " REDIS_PASSWORD
            echo
        done
    else
        # Generate a safe Redis password (alphanumeric only)
        REDIS_PASSWORD=$(openssl rand -hex 16)
    fi
    
    read -p "Setup SSL with Let's Encrypt? (Y/n): " ssl_response
    if [[ ! $ssl_response =~ ^[Nn]$ ]]; then
        SETUP_SSL=true
    fi
    
    read -p "Setup development environment? (y/N): " dev_response
    if [[ $dev_response =~ ^[Yy]$ ]]; then
        APP_ENV="local"
    fi
}

# Update system packages
update_system() {
    log "Updating system packages..."
    sudo apt update
    sudo apt upgrade -y
    sudo apt install -y curl wget gnupg2 software-properties-common apt-transport-https ca-certificates lsb-release
}

# Install PHP 8.4
install_php() {
    log "Installing PHP 8.4..."
    
    sudo add-apt-repository ppa:ondrej/php -y
    sudo apt update
    
    sudo apt install -y php8.4 php8.4-fpm php8.4-cli php8.4-common php8.4-curl \
        php8.4-zip php8.4-gd php8.4-mysql php8.4-xml php8.4-mbstring \
        php8.4-intl php8.4-bcmath php8.4-bz2 php8.4-readline \
        php8.4-pgsql php8.4-redis php8.4-opcache
    
    # Configure PHP
    sudo sed -i 's/;cgi.fix_pathinfo=1/cgi.fix_pathinfo=0/' /etc/php/8.4/fpm/php.ini
    sudo sed -i 's/upload_max_filesize = 2M/upload_max_filesize = 50M/' /etc/php/8.4/fpm/php.ini
    sudo sed -i 's/post_max_size = 8M/post_max_size = 50M/' /etc/php/8.4/fpm/php.ini
    sudo sed -i 's/max_execution_time = 30/max_execution_time = 300/' /etc/php/8.4/fpm/php.ini
    sudo sed -i 's/memory_limit = 128M/memory_limit = 512M/' /etc/php/8.4/fpm/php.ini
    
    sudo systemctl enable php8.4-fpm
    sudo systemctl start php8.4-fpm
}

# Install Composer
install_composer() {
    log "Installing Composer..."
    
    curl -sS https://getcomposer.org/installer | php
    sudo mv composer.phar /usr/local/bin/composer
    sudo chmod +x /usr/local/bin/composer
}

# Install Node.js and npm
install_nodejs() {
    log "Installing Node.js..."
    
    curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
    sudo apt install -y nodejs
}

# Install PostgreSQL 16
install_postgresql() {
    log "Installing PostgreSQL 16..."
    
    sudo sh -c 'echo "deb http://apt.postgresql.org/pub/repos/apt $(lsb_release -cs)-pgdg main" > /etc/apt/sources.list.d/pgdg.list'
    wget --quiet -O - https://www.postgresql.org/media/keys/ACCC4CF8.asc | sudo apt-key add -
    sudo apt update
    sudo apt install -y postgresql-16 postgresql-client-16
    
    sudo systemctl enable postgresql
    sudo systemctl start postgresql
    
    # Create database and user
    sudo -u postgres psql << EOF
CREATE USER ${DB_USER} WITH PASSWORD '${DB_PASSWORD}';
CREATE DATABASE ${DB_NAME} OWNER ${DB_USER};
GRANT ALL PRIVILEGES ON DATABASE ${DB_NAME} TO ${DB_USER};
ALTER USER ${DB_USER} CREATEDB;
\q
EOF
}

# Install Redis 7 with proper configuration
install_redis() {
    log "Installing Redis 7..."
    
    curl -fsSL https://packages.redis.io/gpg | sudo gpg --dearmor -o /usr/share/keyrings/redis-archive-keyring.gpg
    echo "deb [signed-by=/usr/share/keyrings/redis-archive-keyring.gpg] https://packages.redis.io/deb $(lsb_release -cs) main" | sudo tee /etc/apt/sources.list.d/redis.list
    sudo apt update
    sudo apt install -y redis
    
    # Stop Redis before configuration
    sudo systemctl stop redis-server
    
    # Configure Redis properly
    log "Configuring Redis..."
    
    # Backup original config
    sudo cp /etc/redis/redis.conf /etc/redis/redis.conf.backup
    
    # Reset Redis configuration to defaults first
    sudo cp /etc/redis/redis.conf.backup /etc/redis/redis.conf
    
    # Configure Redis for Laravel with proper settings
    sudo sed -i 's/^bind 127.0.0.1 ::1$/bind 127.0.0.1/' /etc/redis/redis.conf
    sudo sed -i 's/^# requirepass foobared$/requirepass '${REDIS_PASSWORD}'/' /etc/redis/redis.conf
    sudo sed -i 's/^protected-mode yes$/protected-mode no/' /etc/redis/redis.conf
    sudo sed -i 's/^# maxmemory <bytes>$/maxmemory 256mb/' /etc/redis/redis.conf
    sudo sed -i 's/^# maxmemory-policy noeviction$/maxmemory-policy allkeys-lru/' /etc/redis/redis.conf
    sudo sed -i 's/^tcp-keepalive 300$/tcp-keepalive 60/' /etc/redis/redis.conf
    sudo sed -i 's/^timeout 0$/timeout 300/' /etc/redis/redis.conf
    
    # Enable and restart Redis
    sudo systemctl enable redis-server
    sudo systemctl start redis-server
    
    # Wait for Redis to start properly
    sleep 5
    
    # Test Redis connection with proper authentication
    log "Testing Redis connection..."
    if redis-cli -h 127.0.0.1 -p 6379 -a "${REDIS_PASSWORD}" ping | grep -q "PONG"; then
        log "Redis connection successful"
    else
        error "Redis connection test failed"
        sudo systemctl status redis-server
        redis-cli -h 127.0.0.1 -p 6379 -a "${REDIS_PASSWORD}" ping
        exit 1
    fi
}

# Install Nginx
install_nginx() {
    log "Installing Nginx..."
    
    sudo apt install -y nginx
    sudo systemctl enable nginx
    sudo systemctl start nginx
    
    # Remove default site
    sudo rm -f /etc/nginx/sites-enabled/default
}

# Setup application directory
setup_app_directory() {
    log "Setting up application directory..."
    
    sudo mkdir -p /var/www/${APP_NAME}
    sudo chown -R $USER:www-data /var/www/${APP_NAME}
    
    # Copy application files
    if [[ -d "$(pwd)" && -f "$(pwd)/composer.json" ]]; then
        log "Copying application files..."
        cp -r . /var/www/${APP_NAME}/
        # Remove .git directory if exists
        rm -rf /var/www/${APP_NAME}/.git
        
        # Ensure artisan is executable
        chmod +x /var/www/${APP_NAME}/artisan
        
        # Verify essential files exist
        if [[ ! -f "/var/www/${APP_NAME}/artisan" ]]; then
            error "Critical file missing: artisan"
            exit 1
        fi
        
        if [[ ! -f "/var/www/${APP_NAME}/public/index.php" ]]; then
            error "Critical file missing: public/index.php"
            exit 1
        fi
        
        if [[ ! -f "/var/www/${APP_NAME}/.env.example" ]]; then
            error "Critical file missing: .env.example"
            exit 1
        fi
        
        if [[ ! -d "/var/www/${APP_NAME}/storage" ]]; then
            error "Critical directory missing: storage"
            exit 1
        fi
        
        if [[ ! -d "/var/www/${APP_NAME}/bootstrap" ]]; then
            error "Critical directory missing: bootstrap"
            exit 1
        fi
        
        log "All essential Laravel files verified successfully"
        
    else
        error "Application files not found in current directory"
        exit 1
    fi
    
    cd /var/www/${APP_NAME}
}

# Install dependencies
install_dependencies() {
    log "Installing PHP dependencies..."
    composer install --no-dev --optimize-autoloader
    
    log "Installing Node.js dependencies..."
    npm install
    npm run build
}

# Configure environment
configure_environment() {
    log "Configuring environment..."
    
    # Copy .env.example to .env
    if [[ -f ".env.example" ]]; then
        cp .env.example .env
        log "Copied .env.example to .env"
    else
        error ".env.example file not found"
        exit 1
    fi
    
    # Generate app key with better error handling
    log "Generating application key..."
    if php artisan key:generate --force >/dev/null 2>&1; then
        log "Application key generated successfully"
    else
        warning "Failed to generate app key with artisan, using openssl fallback"
        APP_KEY="base64:$(openssl rand -base64 32)"
        sed -i "s/APP_KEY=.*/APP_KEY=${APP_KEY}/" .env
    fi
    
    # Update .env file with production values
    sed -i "s/APP_NAME=.*/APP_NAME=\"Next Gold\"/" .env
    sed -i "s/APP_ENV=.*/APP_ENV=${APP_ENV}/" .env
    sed -i "s/APP_DEBUG=.*/APP_DEBUG=false/" .env
    sed -i "s#APP_URL=.*#APP_URL=https://${APP_DOMAIN}#" .env
    sed -i "s/DB_DATABASE=.*/DB_DATABASE=${DB_NAME}/" .env
    sed -i "s/DB_USERNAME=.*/DB_USERNAME=${DB_USER}/" .env
    sed -i "s/DB_PASSWORD=.*/DB_PASSWORD=${DB_PASSWORD}/" .env
    sed -i "s/REDIS_PASSWORD=.*/REDIS_PASSWORD=${REDIS_PASSWORD}/" .env
    sed -i "s/LOG_LEVEL=.*/LOG_LEVEL=error/" .env
    
    # Create storage directories if they don't exist
    sudo mkdir -p /var/www/${APP_NAME}/storage/{app/public,framework/{cache,sessions,views},logs}
    
    # Set correct permissions for all Laravel directories
    log "Setting correct permissions..."
    sudo chown -R www-data:www-data /var/www/${APP_NAME}
    sudo chmod -R 755 /var/www/${APP_NAME}
    sudo chmod -R 775 /var/www/${APP_NAME}/storage
    sudo chmod -R 775 /var/www/${APP_NAME}/bootstrap/cache
    
    # Ensure artisan is executable
    sudo chmod +x /var/www/${APP_NAME}/artisan
    
    # Create storage symlink
    log "Creating storage symlink..."
    if [[ -L "/var/www/${APP_NAME}/public/storage" ]]; then
        sudo rm /var/www/${APP_NAME}/public/storage
    fi
    sudo ln -sf /var/www/${APP_NAME}/storage/app/public /var/www/${APP_NAME}/public/storage
    
    # Verify permissions are set correctly
    if [[ -w "/var/www/${APP_NAME}/storage/logs" && -w "/var/www/${APP_NAME}/bootstrap/cache" ]]; then
        log "Permissions set correctly"
    else
        error "Failed to set correct permissions"
        exit 1
    fi
}

# Setup database
setup_database() {
    log "Setting up database..."
    
    # Clear any existing cache
    log "Clearing existing cache..."
    php artisan config:clear
    php artisan cache:clear
    php artisan view:clear
    php artisan route:clear
    
    # Run migrations and seeders with error handling
    log "Running database migrations..."
    if php artisan migrate --force; then
        log "Migrations completed successfully"
    else
        error "Database migration failed"
        exit 1
    fi
    
    log "Running database seeders..."
    if php artisan db:seed --force; then
        log "Seeders completed successfully"
    else
        error "Database seeding failed"
        exit 1
    fi
    
    # Create storage link if not exists
    php artisan storage:link
    
    # Optimize application for production
    log "Optimizing application for production..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    
    log "Database setup completed successfully"
}

# Configure Nginx
configure_nginx() {
    log "Configuring Nginx..."
    
    cat > /tmp/${APP_NAME}.conf << EOF
server {
    listen 80;
    listen [::]:80;
    server_name ${APP_DOMAIN};
    root /var/www/${APP_NAME}/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Security headers
    add_header X-XSS-Protection "1; mode=block";
    add_header Referrer-Policy "strict-origin-when-cross-origin";
    add_header Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self' data:;";

    # Gzip compression
    gzip on;
    gzip_comp_level 6;
    gzip_types text/plain text/css application/json application/javascript text/xml application/xml application/xml+rss text/javascript;
}
EOF

    sudo mv /tmp/${APP_NAME}.conf /etc/nginx/sites-available/
    sudo ln -sf /etc/nginx/sites-available/${APP_NAME}.conf /etc/nginx/sites-enabled/
    
    sudo nginx -t
    sudo systemctl reload nginx
}

# Setup SSL with Let's Encrypt
setup_ssl() {
    if [[ "$SETUP_SSL" == true ]]; then
        log "Setting up SSL with Let's Encrypt..."
        
        sudo apt install -y certbot python3-certbot-nginx
        
        # Request certificate
        sudo certbot --nginx -d ${APP_DOMAIN} --non-interactive --agree-tos --email admin@${APP_DOMAIN}
        
        # Setup auto-renewal
        echo "0 12 * * * /usr/bin/certbot renew --quiet" | sudo crontab -
    fi
}

# Setup systemd services
setup_systemd() {
    log "Setting up systemd services..."
    
    # Queue worker service
    cat > /tmp/${APP_NAME}-worker.service << EOF
[Unit]
Description=${APP_NAME} queue worker
After=network.target

[Service]
User=www-data
Group=www-data
Restart=always
ExecStart=/usr/bin/php /var/www/${APP_NAME}/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
StandardOutput=journal
StandardError=journal

[Install]
WantedBy=multi-user.target
EOF

    sudo mv /tmp/${APP_NAME}-worker.service /etc/systemd/system/
    sudo systemctl daemon-reload
    sudo systemctl enable ${APP_NAME}-worker
    sudo systemctl start ${APP_NAME}-worker
}

# Setup cron jobs
setup_cron() {
    log "Setting up cron jobs..."
    
    # Laravel scheduler
    (crontab -l 2>/dev/null || true; echo "* * * * * cd /var/www/${APP_NAME} && php artisan schedule:run >> /dev/null 2>&1") | crontab -
}

# Setup log rotation
setup_logrotate() {
    log "Setting up log rotation..."
    
    cat > /tmp/${APP_NAME} << EOF
/var/www/${APP_NAME}/storage/logs/*.log {
    daily
    missingok
    rotate 52
    compress
    delaycompress
    notifempty
    create 644 www-data www-data
}
EOF

    sudo mv /tmp/${APP_NAME} /etc/logrotate.d/
}

# Setup firewall
setup_firewall() {
    log "Configuring firewall..."
    
    sudo ufw allow OpenSSH
    sudo ufw allow 'Nginx Full'
    sudo ufw --force enable
}

# Create backup directories
setup_backup() {
    if [[ "$BACKUP_ENABLED" == true ]]; then
        log "Setting up backup directories..."
        
        sudo mkdir -p /var/backups/${APP_NAME}
        sudo chown -R www-data:www-data /var/backups/${APP_NAME}
        
        # Setup backup cron job
        (crontab -l 2>/dev/null || true; echo "0 2 * * * cd /var/www/${APP_NAME} && php artisan backup:run >> /dev/null 2>&1") | crontab -
    fi
}

# Final setup and optimization
final_setup() {
    log "Performing final setup..."
    
    cd /var/www/${APP_NAME}
    
    # Ensure all caches are cleared and rebuilt
    log "Rebuilding caches..."
    php artisan config:clear
    php artisan cache:clear
    php artisan view:clear
    php artisan route:clear
    
    # Rebuild caches
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    
    # Verify application key is set
    if ! grep -q "APP_KEY=" .env || [[ $(grep "APP_KEY=" .env | cut -d'=' -f2) == "" ]]; then
        log "Generating application key..."
        php artisan key:generate --force
    fi
    
    # Ensure storage symlink exists
    if [[ ! -L "/var/www/${APP_NAME}/public/storage" ]]; then
        php artisan storage:link
    fi
    
    # Final permission check and set
    log "Final permission check..."
    sudo chown -R www-data:www-data /var/www/${APP_NAME}
    sudo chmod -R 755 /var/www/${APP_NAME}
    sudo chmod -R 775 /var/www/${APP_NAME}/storage
    sudo chmod -R 775 /var/www/${APP_NAME}/bootstrap/cache
    
    # Test that Laravel can run basic commands
    if php artisan --version >/dev/null 2>&1; then
        log "Laravel installation verified"
    else
        error "Laravel installation verification failed"
        exit 1
    fi
}

# Health check
health_check() {
    log "Performing health check..."
    
    # Check services
    services=("nginx" "php8.4-fpm" "postgresql" "redis-server" "${APP_NAME}-worker")
    for service in "${services[@]}"; do
        if systemctl is-active --quiet $service; then
            info "✓ $service is running"
        else
            error "✗ $service is not running"
            sudo systemctl status $service
        fi
    done
    
    # Check Redis connection
    log "Testing Redis connection..."
    if redis-cli -h 127.0.0.1 -p 6379 -a "${REDIS_PASSWORD}" ping | grep -q "PONG"; then
        info "✓ Redis connection working"
    else
        error "✗ Redis connection failed"
    fi
    
    # Check PostgreSQL connection
    log "Testing PostgreSQL connection..."
    if sudo -u postgres psql -d ${DB_NAME} -c "SELECT 1;" >/dev/null 2>&1; then
        info "✓ PostgreSQL connection working"
    else
        error "✗ PostgreSQL connection failed"
    fi
    
    # Check application response
    log "Testing application response..."
    if curl -f -s -o /dev/null --max-time 10 "http://localhost"; then
        info "✓ Application is responding on HTTP"
    else
        warning "✗ Application may not be responding correctly on HTTP"
    fi
    
    # Check Laravel artisan commands
    log "Testing Laravel artisan..."
    if cd /var/www/${APP_NAME} && php artisan --version >/dev/null 2>&1; then
        info "✓ Laravel artisan working"
    else
        error "✗ Laravel artisan not working"
    fi
    
    # Check file permissions
    log "Checking file permissions..."
    if [[ -w "/var/www/${APP_NAME}/storage/logs" && -w "/var/www/${APP_NAME}/bootstrap/cache" ]]; then
        info "✓ File permissions correct"
    else
        error "✗ File permissions incorrect"
    fi
}

# Main installation function
main() {
    log "Starting Next Gold installation..."
    
    get_configuration
    update_system
    install_php
    install_composer
    install_nodejs
    install_postgresql
    install_redis
    install_nginx
    setup_app_directory
    install_dependencies
    configure_environment
    setup_database
    configure_nginx
    setup_ssl
    setup_systemd
    setup_cron
    setup_logrotate
    setup_firewall
    setup_backup
    final_setup
    health_check
    
    log "Installation completed successfully!"
    echo
    info "Next Gold is now installed and configured."
    info "You can access it at: https://${APP_DOMAIN}"
    echo
    info "Default admin credentials:"
    info "Username: admin"
    info "Password: password"
    warning "Please change the default password immediately!"
    echo
    info "Important files and directories:"
    info "- Application: /var/www/${APP_NAME}"
    info "- Nginx config: /etc/nginx/sites-available/${APP_NAME}.conf"
    info "- Logs: /var/www/${APP_NAME}/storage/logs"
    if [[ "$BACKUP_ENABLED" == true ]]; then
        info "- Backups: /var/backups/${APP_NAME}"
    fi
    echo
    info "Useful commands:"
    info "- Check application status: sudo systemctl status ${APP_NAME}-worker"
    info "- View logs: tail -f /var/www/${APP_NAME}/storage/logs/laravel.log"
    info "- Run artisan commands: cd /var/www/${APP_NAME} && php artisan <command>"
}

# Run main function
main "$@"
