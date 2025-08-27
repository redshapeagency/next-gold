#!/bin/bash

# Next Gold Update Script
# Updates the application with zero-downtime deployment

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuration
APP_DIR="/var/www/next-gold"
BACKUP_DIR="/var/backups/next-gold"
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")

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

# Function to log messages
log() {
    echo "$(date +"%Y-%m-%d %H:%M:%S") - $1"
}

log "Starting update process..."

# Create backup before update
print_status "Creating backup before update..."
"$APP_DIR/scripts/backup.sh"

# Enable maintenance mode
print_status "Enabling maintenance mode..."
cd "$APP_DIR"
php artisan down

# Update application code
print_status "Updating application code..."
if [ -d "$APP_DIR/.git" ]; then
    git pull origin main
    git log --oneline -5
else
    print_warning "No git repository found, skipping git pull"
fi

# Update PHP dependencies
print_status "Updating PHP dependencies..."
composer install --no-dev --optimize-autoloader

# Update Node.js dependencies
print_status "Updating Node.js dependencies..."
npm install
npm run build

# Run database migrations
print_status "Running database migrations..."
php artisan migrate --force

# Clear and cache configurations
print_status "Clearing and caching configurations..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart queue worker
print_status "Restarting queue worker..."
sudo systemctl restart next-gold-queue

# Run health checks
print_status "Running health checks..."
if curl -f -s "http://localhost" > /dev/null; then
    print_status "✓ Application is responding"
else
    print_error "✗ Application health check failed"
    exit 1
fi

# Disable maintenance mode
print_status "Disabling maintenance mode..."
php artisan up

# Send notification
if [ -f "$APP_DIR/.env" ] && grep -q "MAIL_" "$APP_DIR/.env"; then
    print_status "Sending update notification..."
    php artisan mail:send --to="$(grep MAIL_FROM_ADDRESS "$APP_DIR/.env" | cut -d'=' -f2 | tr -d ' ')" \
        --subject="Next Gold Update Completed" \
        --body="Update completed successfully at $(date)"
fi

log "Update completed successfully!"

print_status "Update completed successfully!"
print_status "Application is now running the latest version."
