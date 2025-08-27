#!/bin/bash

# Next Gold Installation Script
# Idempotent installation for Ubuntu 24.04

# Show help if requested
if [ "$1" = "--help" ] || [ "$1" = "-h" ]; then
    echo "Next Gold Installation Script"
    echo ""
    echo "Usage: $0 [OPTIONS]"
    echo ""
    echo "Options:"
    echo "  --help, -h          Show this help message"
    echo "  --skip-deps         Skip dependency installation (useful if installation was interrupted)"
    echo "  --domain DOMAIN     Set the domain name (default: hostname)"
    echo "  --db-name NAME      Set the database name (default: next_gold)"
    echo "  --db-user USER      Set the database user (default: next_gold)"
    echo ""
    echo "Environment variables:"
    echo "  SKIP_DEPS=true      Skip PHP/Node.js dependency installation"
    echo "  DOMAIN=example.com  Set the domain name"
    echo "  DB_NAME=name        Set the database name"
    echo "  DB_USER=user        Set the database user"
    echo "  DB_PASS=password    Set the database password (auto-generated if not set)"
    echo "  REDIS_PASS=password Set the Redis password (auto-generated if not set)"
    echo ""
    echo "Examples:"
    echo "  $0                           # Normal installation"
    echo "  $0 --skip-deps               # Skip dependency installation"
    echo "  SKIP_DEPS=true $0            # Skip dependencies using environment variable"
    echo "  DOMAIN=example.com $0        # Set custom domain"
    exit 0
fi

# Parse command line arguments
while [[ $# -gt 0 ]]; do
    case $1 in
        --skip-deps)
            SKIP_DEPS=true
            shift
            ;;
        --domain)
            DOMAIN="$2"
            shift 2
            ;;
        --db-name)
            DB_NAME="$2"
            shift 2
            ;;
        --db-user)
            DB_USER="$2"
            shift 2
            ;;
        *)
            print_error "Unknown option: $1"
            echo "Use --help for usage information"
            exit 1
            ;;
    esac
done

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
SKIP_DEPS="${SKIP_DEPS:-false}"  # Set to true to skip dependency installation

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

# Function to check network connectivity
check_network() {
    print_status "Checking network connectivity..."
    if curl -s --max-time 10 https://packagist.org > /dev/null 2>&1; then
        print_status "Network connectivity to Packagist: OK"
    else
        print_warning "Cannot reach Packagist (Composer repository)"
        print_warning "This might cause dependency installation to fail"
    fi

    if curl -s --max-time 10 https://registry.npmjs.org > /dev/null 2>&1; then
        print_status "Network connectivity to npm registry: OK"
    else
        print_warning "Cannot reach npm registry"
        print_warning "This might cause Node.js dependency installation to fail"
    fi
}

# Function to check system requirements
check_requirements() {
    print_status "Checking system requirements..."

    # Check available memory
    local total_mem=$(free -m | awk 'NR==2{printf "%.0f", $2}')
    if [ "$total_mem" -lt 1024 ]; then
        print_warning "System has ${total_mem}MB RAM. At least 1GB is recommended for dependency installation."
    else
        print_status "System memory: ${total_mem}MB - OK"
    fi

    # Check available disk space
    local available_space=$(df / | awk 'NR==2{printf "%.0f", $4/1024}')
    if [ "$available_space" -lt 1024 ]; then
        print_warning "Only ${available_space}MB free space available. At least 1GB is recommended."
    else
        print_status "Available disk space: ${available_space}MB - OK"
    fi
}

# Function to check if command exists
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Function to handle script interruption
cleanup_on_interrupt() {
    print_warning "Installation interrupted by user"
    print_warning "You can continue the installation later by running:"
    echo "  cd $APP_DIR"
    echo "  composer install --no-dev --optimize-autoloader"
    echo "  npm install && npm run build"
    echo "  Then run the install script again with SKIP_DEPS=true:"
    echo "  SKIP_DEPS=true bash /path/to/install.sh"
    exit 1
}

# Set up interrupt handler
trap cleanup_on_interrupt INT TERM

# Pre-installation checks
check_requirements
check_network

# Update system
print_status "Updating system packages..."
apt update && apt upgrade -y

# Add PHP repository
print_status "Adding PHP repository..."
if ! apt-cache policy | grep -q ondrej/php; then
    apt install -y software-properties-common
    add-apt-repository ppa:ondrej/php -y
    apt update
fi

# Function to install PHP packages
install_php_packages() {
    local php_version=$1
    print_status "Installing PHP $php_version packages..."

    # Try to install PHP packages
    if apt install -y \
        php${php_version} \
        php${php_version}-cli \
        php${php_version}-fpm \
        php${php_version}-pgsql \
        php${php_version}-redis \
        php${php_version}-gd \
        php${php_version}-bcmath \
        php${php_version}-xml \
        php${php_version}-curl \
        php${php_version}-dom \
        php${php_version}-zip \
        php${php_version}-fileinfo \
        php${php_version}-mbstring \
        php${php_version}-intl; then
        print_status "PHP $php_version installed successfully"
        return 0
    else
        print_warning "PHP $php_version installation failed"
        return 1
    fi
}

# Try to install PHP 8.4, fallback to 8.3, then 8.2
PHP_VERSION=""
if install_php_packages "8.4"; then
    PHP_VERSION="8.4"
elif install_php_packages "8.3"; then
    PHP_VERSION="8.3"
elif install_php_packages "8.2"; then
    PHP_VERSION="8.2"
else
    print_error "Failed to install any supported PHP version (8.4, 8.3, 8.2)"
    exit 1
fi

# Install remaining packages
print_status "Installing remaining packages..."
apt install -y \
    curl \
    wget \
    git \
    unzip \
    nginx \
    postgresql \
    postgresql-contrib \
    redis-server \
    composer \
    nodejs \
    npm \
    certbot \
    python3-certbot-nginx

# Verify PHP installation
print_status "Verifying PHP installation..."
if command_exists php; then
    INSTALLED_PHP_VERSION=$(php -r "echo PHP_VERSION;")
    print_status "PHP $INSTALLED_PHP_VERSION installed successfully"

    # Check if PHP version is compatible with Laravel 11 (requires PHP 8.2+)
    PHP_MAJOR=$(php -r "echo PHP_MAJOR_VERSION;")
    PHP_MINOR=$(php -r "echo PHP_MINOR_VERSION;")

    if [ "$PHP_MAJOR" -lt 8 ] || ([ "$PHP_MAJOR" -eq 8 ] && [ "$PHP_MINOR" -lt 2 ]); then
        print_error "PHP $INSTALLED_PHP_VERSION is not compatible with Laravel 11 (requires PHP 8.2+)"
        exit 1
    fi
else
    print_error "PHP installation failed"
    exit 1
fi

# Configure PostgreSQL
print_status "Configuring PostgreSQL..."
if sudo -u postgres createuser --createdb --login --pwprompt "$DB_USER" <<< "$DB_PASS" 2>/dev/null; then
    print_status "PostgreSQL user created successfully"
else
    print_warning "PostgreSQL user may already exist, continuing..."
fi

if sudo -u postgres createdb --owner="$DB_USER" "$DB_NAME" 2>/dev/null; then
    print_status "PostgreSQL database created successfully"
else
    print_warning "PostgreSQL database may already exist, continuing..."
fi

# Configure Redis
print_status "Configuring Redis..."
if [ -f /etc/redis/redis.conf ]; then
    sed -i 's/# requirepass foobared/requirepass '"$REDIS_PASS"'/' /etc/redis/redis.conf
    if systemctl restart redis-server 2>/dev/null; then
        print_status "Redis configured and restarted"
    else
        print_warning "Redis restart failed"
    fi
else
    print_warning "Redis configuration file not found, skipping Redis setup"
fi

# Create application directory
print_status "Creating application directory..."
if mkdir -p "$APP_DIR" 2>/dev/null; then
    chown -R www-data:www-data "$APP_DIR" 2>/dev/null
    print_status "Application directory created successfully"
else
    print_error "Failed to create application directory"
    exit 1
fi

# Clone or update application
if [ -d "$APP_DIR/.git" ]; then
    print_status "Updating application..."
    cd "$APP_DIR"
    if git pull origin main 2>/dev/null; then
        print_status "Application updated successfully"
    else
        print_warning "Failed to update application, continuing with existing version"
    fi
else
    print_status "Cloning application..."
    if git clone https://github.com/redshapeagency/next-gold.git "$APP_DIR" 2>/dev/null; then
        cd "$APP_DIR"
        chown -R www-data:www-data .
        print_status "Application cloned successfully"
    else
        print_error "Failed to clone application repository"
        exit 1
    fi
fi

# Install PHP dependencies
print_status "Installing PHP dependencies..."
cd "$APP_DIR"

if [ "$SKIP_DEPS" = "true" ]; then
    print_warning "Skipping PHP dependency installation (SKIP_DEPS=true)"
    print_warning "Remember to run 'composer install --no-dev --optimize-autoloader' manually"
else
    # Check if Composer is available
    if ! command_exists composer; then
        print_error "Composer is not installed or not in PATH"
        print_warning "You can install Composer manually or set SKIP_DEPS=true to skip this step"
        exit 1
    fi

    if [ -f "composer.json" ]; then
        print_status "Installing Composer dependencies (this may take a few minutes)..."
        echo "  Tip: If this takes too long, you can cancel with Ctrl+C and run:"
        echo "  cd $APP_DIR && composer install --no-dev --optimize-autoloader"

        # Set Composer to non-interactive mode and increase timeout
        export COMPOSER_NO_INTERACTION=1
        export COMPOSER_PROCESS_TIMEOUT=300

        # Try to install dependencies with verbose output for debugging
        if timeout 900 composer install --no-dev --optimize-autoloader --no-progress --prefer-dist 2>&1; then
            print_status "PHP dependencies installed successfully"
        else
            print_error "Failed to install PHP dependencies after 15 minutes"
            print_warning "This might be due to:"
            echo "  - Network connectivity issues"
            echo "  - Memory limitations"
            echo "  - Dependency conflicts"
            echo ""
            print_warning "You can try to install dependencies manually:"
            echo "  cd $APP_DIR"
            echo "  composer install --no-dev --optimize-autoloader"
            echo ""
            print_warning "Or run the script again with SKIP_DEPS=true:"
            echo "  SKIP_DEPS=true bash install.sh"
            exit 1
        fi
    else
        print_error "composer.json not found"
        exit 1
    fi
fi

# Install Node.js dependencies
print_status "Installing Node.js dependencies..."
if [ "$SKIP_DEPS" = "true" ]; then
    print_warning "Skipping Node.js dependency installation (SKIP_DEPS=true)"
    print_warning "Remember to run 'npm install && npm run build' manually"
else
    if [ -f "package.json" ]; then
        print_status "Installing npm dependencies (this may take a few minutes)..."
        echo "  Tip: If this takes too long, you can cancel with Ctrl+C and run:"
        echo "  cd $APP_DIR && npm install && npm run build"

        # Try to install dependencies with timeout
        if timeout 300 npm install --silent 2>&1; then
            print_status "Node.js dependencies installed successfully"

            # Build assets
            print_status "Building assets..."
            if timeout 300 npm run build 2>&1; then
                print_status "Assets built successfully"
            else
                print_error "Failed to build assets after 5 minutes"
                print_warning "You can try to build assets manually:"
                echo "  cd $APP_DIR"
                echo "  npm run build"
                echo ""
                print_warning "Or run the script again with SKIP_DEPS=true:"
                echo "  SKIP_DEPS=true bash install.sh"
                exit 1
            fi
        else
            print_error "Failed to install Node.js dependencies after 5 minutes"
            print_warning "This might be due to:"
            echo "  - Network connectivity issues"
            echo "  - npm registry problems"
            echo "  - Memory limitations"
            echo ""
            print_warning "You can try to install dependencies manually:"
            echo "  cd $APP_DIR"
            echo "  npm install && npm run build"
            echo ""
            print_warning "Or run the script again with SKIP_DEPS=true:"
            echo "  SKIP_DEPS=true bash install.sh"
            exit 1
        fi
    else
        print_error "package.json not found"
        exit 1
    fi
fi

# Configure environment
print_status "Configuring environment..."
if [ -f ".env.example" ]; then
    cp .env.example .env
    sed -i "s/APP_NAME=.*/APP_NAME=\"$APP_NAME\"/" .env
    sed -i "s/APP_KEY=.*/APP_KEY=base64:$APP_KEY/" .env
    sed -i "s/DB_DATABASE=.*/DB_DATABASE=$DB_NAME/" .env
    sed -i "s/DB_USERNAME=.*/DB_USERNAME=$DB_USER/" .env
    sed -i "s/DB_PASSWORD=.*/DB_PASSWORD=$DB_PASS/" .env
    sed -i "s/REDIS_PASSWORD=.*/REDIS_PASSWORD=$REDIS_PASS/" .env
    sed -i "s/APP_URL=.*/APP_URL=https:\/\/$DOMAIN/" .env
    print_status "Environment configured successfully"
else
    print_error ".env.example not found"
    exit 1
fi

# Generate application key
print_status "Generating application key..."
if php artisan key:generate 2>/dev/null; then
    print_status "Application key generated successfully"
else
    print_error "Failed to generate application key"
    exit 1
fi

# Run migrations and seeders
print_status "Running database migrations..."
cd "$APP_DIR"
if php artisan migrate --force 2>/dev/null; then
    print_status "Database migrations completed successfully"
else
    print_error "Database migrations failed"
    exit 1
fi

if php artisan db:seed --force 2>/dev/null; then
    print_status "Database seeded successfully"
else
    print_warning "Database seeding failed, but continuing..."
fi

# Set up storage permissions
print_status "Setting up storage permissions..."
chmod -R 775 storage bootstrap/cache 2>/dev/null || print_warning "Some storage permissions may not be set correctly"
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || print_warning "Some storage ownership may not be set correctly"

# Configure Nginx
print_status "Configuring Nginx..."
if [ -f "scripts/nginx/next-gold.conf.tpl" ]; then
    cp scripts/nginx/next-gold.conf.tpl /etc/nginx/sites-available/$APP_NAME
    sed -i "s/{{DOMAIN}}/$DOMAIN/g" /etc/nginx/sites-available/$APP_NAME
    sed -i "s/{{APP_DIR}}/$APP_DIR/g" /etc/nginx/sites-available/$APP_NAME
    ln -sf /etc/nginx/sites-available/$APP_NAME /etc/nginx/sites-enabled/
    rm -f /etc/nginx/sites-enabled/default

    if nginx -t 2>/dev/null; then
        systemctl reload nginx
        print_status "Nginx configured and reloaded"
    else
        print_error "Nginx configuration test failed"
        exit 1
    fi
else
    print_warning "Nginx configuration template not found, skipping Nginx setup"
fi

# Configure systemd service for queues
print_status "Configuring queue service..."
if [ -f "scripts/systemd/next-gold-queue.service" ]; then
    cp scripts/systemd/next-gold-queue.service /etc/systemd/system/
    sed -i "s/{{APP_DIR}}/$APP_DIR/g" /etc/systemd/system/next-gold-queue.service
    sed -i "s/{{USER}}/$(whoami)/g" /etc/systemd/system/next-gold-queue.service
    systemctl daemon-reload
    systemctl enable next-gold-queue
    systemctl start next-gold-queue
    print_status "Queue service configured and started"
else
    print_warning "Queue service file not found, skipping queue setup"
fi

# Configure cron for scheduled tasks
print_status "Configuring cron jobs..."
CRON_JOB="* * * * * cd $APP_DIR && php artisan schedule:run >> /dev/null 2>&1"
if (crontab -l 2>/dev/null; echo "$CRON_JOB") | crontab - 2>/dev/null; then
    print_status "Cron job configured successfully"
else
    print_warning "Failed to configure cron job"
fi

# Set up SSL certificate
print_status "Setting up SSL certificate..."
if certbot --nginx -d "$DOMAIN" --non-interactive --agree-tos --email "admin@$DOMAIN" 2>/dev/null; then
    print_status "SSL certificate configured successfully"
else
    print_warning "SSL certificate setup failed (this is normal if DNS is not configured)"
fi

# Create backup directory
print_status "Creating backup directory..."
if mkdir -p /var/backups/$APP_NAME 2>/dev/null; then
    chown www-data:www-data /var/backups/$APP_NAME 2>/dev/null
    print_status "Backup directory created successfully"
else
    print_warning "Failed to create backup directory"
fi

# Set up log rotation
print_status "Setting up log rotation..."
if cat > /etc/logrotate.d/$APP_NAME << EOF 2>/dev/null; then
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
    print_status "Log rotation configured successfully"
else
    print_warning "Failed to configure log rotation"
fi

# Final setup steps
print_status "Running final setup..."
php artisan config:cache 2>/dev/null || print_warning "Config cache failed"
php artisan route:cache 2>/dev/null || print_warning "Route cache failed"
php artisan view:cache 2>/dev/null || print_warning "View cache failed"

# Verify services status
print_status "Verifying services status..."
SERVICES_STATUS=""

if systemctl is-active --quiet nginx 2>/dev/null; then
    SERVICES_STATUS="$SERVICES_STATUS✓ Nginx: Running\n"
else
    SERVICES_STATUS="$SERVICES_STATUS✗ Nginx: Not running\n"
fi

if systemctl is-active --quiet postgresql 2>/dev/null; then
    SERVICES_STATUS="$SERVICES_STATUS✓ PostgreSQL: Running\n"
else
    SERVICES_STATUS="$SERVICES_STATUS✗ PostgreSQL: Not running\n"
fi

if systemctl is-active --quiet redis-server 2>/dev/null; then
    SERVICES_STATUS="$SERVICES_STATUS✓ Redis: Running\n"
else
    SERVICES_STATUS="$SERVICES_STATUS✗ Redis: Not running\n"
fi

if systemctl is-active --quiet next-gold-queue 2>/dev/null; then
    SERVICES_STATUS="$SERVICES_STATUS✓ Queue Service: Running\n"
else
    SERVICES_STATUS="$SERVICES_STATUS✗ Queue Service: Not running\n"
fi

# Create admin user if setup is not completed
if php artisan tinker --execute="echo \App\Models\User::count();" 2>/dev/null | grep -q "0"; then
    print_status "Creating default admin user..."
    if php artisan tinker --execute="
    \App\Models\User::create([
        'username' => 'admin',
        'email' => 'admin@$DOMAIN',
        'password' => bcrypt('password'),
        'first_name' => 'Administrator',
        'last_name' => 'System',
        'is_active' => true
    ]);
    " 2>/dev/null; then
        print_status "Default admin user created successfully"
    else
        print_warning "Failed to create default admin user"
    fi
else
    print_status "Admin user already exists, skipping creation"
fi

print_status "Installation completed successfully!"
echo
echo "Installation Summary:"
echo "===================="
echo "✓ PHP Version: $PHP_VERSION"
echo "✓ Domain: $DOMAIN"
echo "✓ Application Directory: $APP_DIR"
echo "✓ Database: $DB_NAME"
echo "✓ Backup Directory: /var/backups/$APP_NAME"
echo
echo "Services Status:"
echo "$SERVICES_STATUS"
echo
echo "Next steps:"
echo "1. Access the application at: https://$DOMAIN"
echo "2. Complete the setup wizard if this is the first installation"
echo "3. Change the default admin password (username: admin, password: password)"
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
echo "Useful commands:"
echo "  Check system status: $APP_DIR/scripts/checks.sh"
echo "  Create backup: $APP_DIR/scripts/backup.sh"
echo "  Update application: $APP_DIR/scripts/update.sh"
echo "  Monitor system: $APP_DIR/scripts/monitor.sh"
echo
print_warning "Please save these credentials securely!"
print_warning "Remember to change the default admin password!"

if [ "$SKIP_DEPS" = "true" ]; then
    echo
    print_warning "⚠️  Dependency installation was skipped!"
    echo "Complete the installation by running:"
    echo "  cd $APP_DIR"
    echo "  composer install --no-dev --optimize-autoloader"
    echo "  npm install && npm run build"
    echo "  Then run the remaining setup:"
    echo "  SKIP_DEPS=true bash install.sh"
fi
