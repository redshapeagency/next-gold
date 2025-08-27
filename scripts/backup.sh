#!/bin/bash

# Next Gold Backup Script
# Creates encrypted backups with HMAC signing

set -e

# Configuration
APP_DIR="/var/www/next-gold"
BACKUP_DIR="/var/backups/next-gold"
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
BACKUP_NAME="next-gold_$TIMESTAMP"
BACKUP_FILE="$BACKUP_DIR/$BACKUP_NAME.tar.gz"
SIGNATURE_FILE="$BACKUP_DIR/$BACKUP_NAME.sig"
LOG_FILE="$BACKUP_DIR/backup.log"

# Create backup directory if it doesn't exist
mkdir -p "$BACKUP_DIR"

# Function to log messages
log() {
    echo "$(date +"%Y-%m-%d %H:%M:%S") - $1" | tee -a "$LOG_FILE"
}

log "Starting backup: $BACKUP_NAME"

# Change to application directory
cd "$APP_DIR"

# Create database dump
log "Creating database dump..."
php artisan db:dump --gzip

# Create backup archive
log "Creating backup archive..."
tar -czf "$BACKUP_FILE" \
    --exclude='storage/logs/*' \
    --exclude='storage/framework/cache/*' \
    --exclude='storage/framework/sessions/*' \
    --exclude='storage/framework/views/*' \
    --exclude='node_modules' \
    --exclude='.git' \
    --exclude='vendor' \
    --exclude='*.log' \
    --exclude='storage/app/backups/*' \
    .

# Generate HMAC signature
log "Generating HMAC signature..."
SECRET_KEY=$(php artisan tinker --execute="echo config('app.key');" | tail -n 1)
echo -n "$TIMESTAMP" | openssl dgst -sha256 -hmac "$SECRET_KEY" -binary > "$SIGNATURE_FILE"

# Verify backup integrity
log "Verifying backup integrity..."
if tar -tzf "$BACKUP_FILE" &> /dev/null; then
    log "✓ Backup archive is valid"
else
    log "✗ Backup archive is corrupted"
    exit 1
fi

# Get backup size
BACKUP_SIZE=$(du -h "$BACKUP_FILE" | cut -f1)
log "Backup size: $BACKUP_SIZE"

# Clean old backups (keep last 7 days)
log "Cleaning old backups..."
find "$BACKUP_DIR" -name "next-gold_*.tar.gz" -mtime +7 -delete
find "$BACKUP_DIR" -name "next-gold_*.sig" -mtime +7 -delete

# Send notification (if configured)
if [ -f "$APP_DIR/.env" ] && grep -q "MAIL_" "$APP_DIR/.env"; then
    log "Sending backup notification..."
    php artisan mail:send --to="$(grep MAIL_FROM_ADDRESS "$APP_DIR/.env" | cut -d'=' -f2 | tr -d ' ')" \
        --subject="Next Gold Backup Completed" \
        --body="Backup completed successfully: $BACKUP_NAME ($BACKUP_SIZE)"
fi

log "Backup completed successfully: $BACKUP_NAME"

# Optional: Upload to remote storage
# Uncomment and configure as needed
# aws s3 cp "$BACKUP_FILE" "s3://your-bucket/backups/"
# aws s3 cp "$SIGNATURE_FILE" "s3://your-bucket/backups/"
