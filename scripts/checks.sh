#!/bin/bash

# Next Gold System Checks Script
# Run this script to verify system health

echo "=== Next Gold System Checks ==="
echo

# Check PHP
echo "1. Checking PHP..."
if command -v php &> /dev/null; then
    PHP_VERSION=$(php -r "echo PHP_VERSION;")
    echo "✓ PHP $PHP_VERSION installed"
else
    echo "✗ PHP not found"
fi
echo

# Check PostgreSQL
echo "2. Checking PostgreSQL..."
if command -v psql &> /dev/null; then
    if sudo -u postgres psql -c "SELECT version();" &> /dev/null; then
        PG_VERSION=$(sudo -u postgres psql -c "SELECT version();" | grep PostgreSQL)
        echo "✓ PostgreSQL installed: $PG_VERSION"
    else
        echo "✗ PostgreSQL connection failed"
    fi
else
    echo "✗ PostgreSQL client not found"
fi
echo

# Check Redis
echo "3. Checking Redis..."
if command -v redis-cli &> /dev/null; then
    if redis-cli ping &> /dev/null; then
        echo "✓ Redis server running"
    else
        echo "✗ Redis server not responding"
    fi
else
    echo "✗ Redis client not found"
fi
echo

# Check Nginx
echo "4. Checking Nginx..."
if command -v nginx &> /dev/null; then
    if systemctl is-active --quiet nginx; then
        echo "✓ Nginx service running"
    else
        echo "✗ Nginx service not running"
    fi
else
    echo "✗ Nginx not found"
fi
echo

# Check Node.js
echo "5. Checking Node.js..."
if command -v node &> /dev/null; then
    NODE_VERSION=$(node -v)
    echo "✓ Node.js $NODE_VERSION installed"
else
    echo "✗ Node.js not found"
fi
echo

# Check Composer
echo "6. Checking Composer..."
if command -v composer &> /dev/null; then
    COMPOSER_VERSION=$(composer --version | head -n 1)
    echo "✓ Composer installed: $COMPOSER_VERSION"
else
    echo "✗ Composer not found"
fi
echo

# Check application directory
echo "7. Checking application directory..."
APP_DIR="/var/www/next-gold"
if [ -d "$APP_DIR" ]; then
    echo "✓ Application directory exists: $APP_DIR"

    # Check if Laravel is installed
    if [ -f "$APP_DIR/artisan" ]; then
        echo "✓ Laravel artisan found"
    else
        echo "✗ Laravel artisan not found"
    fi

    # Check storage permissions
    if [ -w "$APP_DIR/storage" ]; then
        echo "✓ Storage directory writable"
    else
        echo "✗ Storage directory not writable"
    fi
else
    echo "✗ Application directory not found: $APP_DIR"
fi
echo

# Check ports
echo "8. Checking ports..."
PORTS=(80 443 5432 6379)
for port in "${PORTS[@]}"; do
    if lsof -Pi :$port -sTCP:LISTEN -t >/dev/null; then
        SERVICE=$(lsof -Pi :$port -sTCP:LISTEN | head -n 1 | awk '{print $1}')
        echo "✓ Port $port open (used by $SERVICE)"
    else
        echo "✗ Port $port closed"
    fi
done
echo

# Check SSL certificate (if domain is configured)
echo "9. Checking SSL certificate..."
if [ -f "/etc/letsencrypt/live/$(hostname -f)/fullchain.pem" ]; then
    CERT_EXPIRY=$(openssl x509 -in /etc/letsencrypt/live/$(hostname -f)/fullchain.pem -noout -enddate | cut -d= -f2)
    echo "✓ SSL certificate valid until: $CERT_EXPIRY"
else
    echo "! No SSL certificate found (this is normal if not configured)"
fi
echo

echo "=== System checks completed ==="
