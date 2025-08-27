#!/bin/bash

# Next Gold Installation Script
# Idempotent installation for Ubuntu 24.04

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuration
APP_NAME="next-gold"
APP_DIR="/var/www/$APP_NAME"
DOMAIN="${DOMAIN:-$(hostname -f)}"
DB_NAME="${DB_NAME:-next_gold}"
DB_USER="${DB_USER:-next_gold}"
DB_PASS="${DB_PASS:-$(openssl rand -base64 12)}"
REDIS_PASS="${REDIS_PASS:-$(openssl rand -base64 12)}"
APP_KEY="${APP_KEY:-$(openssl rand -base64 32)}"

# Function to print colored output
print_status() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARN]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Function to check if command exists
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Update system
print_status "Updating system packages..."
apt update && apt upgrade -y

# Install required packages
print_status "Installing required packages..."
apt install -y \
    software-properties-common \
    curl \
    wget \
    git \
    unzip \
    nginx \
    postgresql \
    postgresql-contrib \
    redis-server \
    php8.4 \
    php8.4-cli \
    php8.4-fpm \
    php8.4-pgsql \
    php8.4-redis \
    php8.4-gd \
    php8.4-bcmath \
    php8.4-xml \
    php8.4-curl \
    php8.4-dom \
    php8.4-zip \
    php8.4-fileinfo \
    php8.4-mbstring \
    php8.4-intl \
    composer \
    nodejs \
    npm \
    certbot \
    python3-certbot-nginx

# Install Node.js 20 if not available
if ! command_exists node || [[ $(node -v) != v20* ]]; then
    print_status "Installing Node.js 20..."
    curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
    apt-get install -y nodejs
fi

# Configure PostgreSQL
print_status "Configuring PostgreSQL..."
sudo -u postgres createuser --createdb --login --pwprompt "$DB_USER" <<< "$DB_PASS" || true
sudo -u postgres createdb --owner="$DB_USER" "$DB_NAME" || true

# Configure Redis
print_status "Configuring Redis..."
sed -i 's/# requirepass foobared/requirepass '"$REDIS_PASS"'/' /etc/redis/redis.conf
systemctl restart redis-server

# Create application directory
print_status "Creating application directory..."
mkdir -p "$APP_DIR"
chown -R www-data:www-data "$APP_DIR"

# Clone or update application
if [ -d "$APP_DIR/.git" ]; then
    print_status "Updating application..."
    cd "$APP_DIR"
    git pull origin main
else
    print_status "Cloning application..."
    git clone https://github.com/redshapeagency/next-gold.git "$APP_DIR"
    cd "$APP_DIR"
    chown -R www-data:www-data .
fi

# Install PHP dependencies
print_status "Installing PHP dependencies..."
cd "$APP_DIR"
composer install --no-dev --optimize-autoloader

# Install Node.js dependencies
print_status "Installing Node.js dependencies..."
npm install
npm run build

# Configure environment
print_status "Configuring environment..."
cp .env.example .env
sed -i "s/APP_NAME=.*/APP_NAME=\"$APP_NAME\"/" .env
sed -i "s/APP_KEY=.*/APP_KEY=base64:$APP_KEY/" .env
sed -i "s/DB_DATABASE=.*/DB_DATABASE=$DB_NAME/" .env
sed -i "s/DB_USERNAME=.*/DB_USERNAME=$DB_USER/" .env
sed -i "s/DB_PASSWORD=.*/DB_PASSWORD=$DB_PASS/" .env
sed -i "s/REDIS_PASSWORD=.*/REDIS_PASSWORD=$REDIS_PASS/" .env
sed -i "s/APP_URL=.*/APP_URL=https:\/\/$DOMAIN/" .env

# Generate application key
print_status "Generating application key..."
php artisan key:generate

# Run migrations and seeders
print_status "Running database migrations..."
php artisan migrate --force
php artisan db:seed --force

# Set up storage permissions
print_status "Setting up storage permissions..."
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Configure Nginx
print_status "Configuring Nginx..."
cp scripts/nginx/next-gold.conf.tpl /etc/nginx/sites-available/$APP_NAME
sed -i "s/{{DOMAIN}}/$DOMAIN/g" /etc/nginx/sites-available/$APP_NAME
sed -i "s/{{APP_DIR}}/$APP_DIR/g" /etc/nginx/sites-available/$APP_NAME
ln -sf /etc/nginx/sites-available/$APP_NAME /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default
nginx -t
systemctl reload nginx

# Configure systemd service for queues
print_status "Configuring queue service..."
cp scripts/systemd/next-gold-queue.service /etc/systemd/system/
sed -i "s/{{APP_DIR}}/$APP_DIR/g" /etc/systemd/system/next-gold-queue.service
sed -i "s/{{USER}}/$(whoami)/g" /etc/systemd/system/next-gold-queue.service
systemctl daemon-reload
systemctl enable next-gold-queue
systemctl start next-gold-queue

# Configure cron for scheduled tasks
print_status "Configuring cron jobs..."
CRON_JOB="* * * * * cd $APP_DIR && php artisan schedule:run >> /dev/null 2>&1"
(crontab -l ; echo "$CRON_JOB") | crontab -

# Set up SSL certificate
print_status "Setting up SSL certificate..."
certbot --nginx -d "$DOMAIN" --non-interactive --agree-tos --email "admin@$DOMAIN" || true

# Create backup directory
print_status "Creating backup directory..."
mkdir -p /var/backups/$APP_NAME
chown www-data:www-data /var/backups/$APP_NAME

# Set up log rotation
print_status "Setting up log rotation..."
cat > /etc/logrotate.d/$APP_NAME << EOF
$APP_DIR/storage/logs/*.log {
    daily
    missingok
    rotate 7
    compress
    notifempty
    create 0644 www-data www-data
    postrotate
        systemctl reload nginx
    endscript
}
EOF

# Final setup steps
print_status "Running final setup..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Create admin user if setup is not completed
if ! php artisan tinker --execute="echo \App\Models\User::count();" | grep -q "1"; then
    print_status "Creating default admin user..."
    php artisan tinker --execute="
    \App\Models\User::create([
        'username' => 'admin',
        'email' => 'admin@$DOMAIN',
        'password' => bcrypt('password'),
        'first_name' => 'Administrator',
        'last_name' => 'System',
        'is_active' => true
    ]);
    "
fi

print_status "Installation completed successfully!"
echo
echo "Next steps:"
echo "1. Access the application at: https://$DOMAIN"
echo "2. Complete the setup wizard if this is the first installation"
echo "3. Change the default admin password"
echo "4. Configure gold price API settings"
echo "5. Set up monitoring and alerts"
echo
echo "Database credentials:"
echo "  Database: $DB_NAME"
echo "  Username: $DB_USER"
echo "  Password: $DB_PASS"
echo
echo "Redis password: $REDIS_PASS"
echo
print_warning "Please save these credentials securely!"
