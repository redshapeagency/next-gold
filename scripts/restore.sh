#!/bin/bash

# Next Gold Restore Script
# Restores from encrypted backups with HMAC verification

set -e

# Configuration
APP_DIR="/var/www/next-gold"
BACKUP_DIR="/var/backups/next-gold"
LOG_FILE="$BACKUP_DIR/restore.log"

# Function to log messages
log() {
    echo "$(date +"%Y-%m-%d %H:%M:%S") - $1" | tee -a "$LOG_FILE"
}

# Function to show usage
usage() {
    echo "Usage: $0 <backup_name>"
    echo "Available backups:"
    ls -la "$BACKUP_DIR"/next-gold_*.tar.gz | awk '{print "  " $9 " (" $5 " bytes)"}'
    exit 1
}

# Check arguments
if [ $# -ne 1 ]; then
    usage
fi

BACKUP_NAME="$1"
BACKUP_FILE="$BACKUP_DIR/$BACKUP_NAME.tar.gz"
SIGNATURE_FILE="$BACKUP_DIR/$BACKUP_NAME.sig"

# Check if backup exists
if [ ! -f "$BACKUP_FILE" ]; then
    log "✗ Backup file not found: $BACKUP_FILE"
    exit 1
fi

if [ ! -f "$SIGNATURE_FILE" ]; then
    log "✗ Signature file not found: $SIGNATURE_FILE"
    exit 1
fi

log "Starting restore: $BACKUP_NAME"

# Verify HMAC signature
log "Verifying HMAC signature..."
TIMESTAMP=$(echo "$BACKUP_NAME" | cut -d'_' -f2-3)
SECRET_KEY=$(cd "$APP_DIR" && php artisan tinker --execute="echo config('app.key');" | tail -n 1)
EXPECTED_SIGNATURE=$(echo -n "$TIMESTAMP" | openssl dgst -sha256 -hmac "$SECRET_KEY" -binary | xxd -p -c 256)

ACTUAL_SIGNATURE=$(xxd -p -c 256 "$SIGNATURE_FILE")

if [ "$EXPECTED_SIGNATURE" != "$ACTUAL_SIGNATURE" ]; then
    log "✗ HMAC signature verification failed"
    exit 1
fi

log "✓ HMAC signature verified"

# Create restore directory
RESTORE_DIR="$APP_DIR.restore.$(date +%s)"
mkdir -p "$RESTORE_DIR"

# Extract backup
log "Extracting backup..."
tar -xzf "$BACKUP_FILE" -C "$RESTORE_DIR"

# Stop services
log "Stopping services..."
sudo systemctl stop nginx
sudo systemctl stop next-gold-queue

# Backup current application
log "Backing up current application..."
CURRENT_BACKUP="$APP_DIR.backup.$(date +%s)"
sudo mv "$APP_DIR" "$CURRENT_BACKUP"

# Move restored application
log "Moving restored application..."
sudo mv "$RESTORE_DIR" "$APP_DIR"
sudo chown -R www-data:www-data "$APP_DIR"
sudo chmod -R 755 "$APP_DIR"
sudo chmod -R 775 "$APP_DIR/storage"
sudo chmod -R 775 "$APP_DIR/bootstrap/cache"

# Restore database
log "Restoring database..."
cd "$APP_DIR"
php artisan db:restore

# Clear caches
log "Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Run migrations (in case of schema changes)
log "Running migrations..."
php artisan migrate --force

# Build assets
log "Building assets..."
npm install
npm run build

# Start services
log "Starting services..."
sudo systemctl start next-gold-queue
sudo systemctl start nginx

# Verify application
log "Verifying application..."
if curl -f -s "http://localhost" > /dev/null; then
    log "✓ Application is running"
else
    log "✗ Application verification failed"
fi

# Clean up old backup
log "Cleaning up..."
sudo rm -rf "$CURRENT_BACKUP"

log "Restore completed successfully: $BACKUP_NAME"

# Send notification
if [ -f "$APP_DIR/.env" ] && grep -q "MAIL_" "$APP_DIR/.env"; then
    log "Sending restore notification..."
    php artisan mail:send --to="$(grep MAIL_FROM_ADDRESS "$APP_DIR/.env" | cut -d'=' -f2 | tr -d ' ')" \
        --subject="Next Gold Restore Completed" \
        --body="Restore completed successfully: $BACKUP_NAME"
fi
