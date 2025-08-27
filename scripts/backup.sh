#!/bin/bash

# Next Gold Backup Script
# Creates automated backups of the application

set -e

# Configuration
APP_PATH="/var/www/next-gold"
BACKUP_PATH="/var/backups/next-gold"
RETENTION_DAYS=30
TIMESTAMP=$(date +%Y%m%d_%H%M%S)

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

log() {
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')] $1${NC}"
}

error() {
    echo -e "${RED}[ERROR] $1${NC}" >&2
}

# Create backup directory
mkdir -p "$BACKUP_PATH"

cd "$APP_PATH"

# Function to create database backup
backup_database() {
    log "Creating database backup..."
    
    DB_NAME=$(grep DB_DATABASE .env | cut -d'=' -f2)
    DB_USER=$(grep DB_USERNAME .env | cut -d'=' -f2)
    
    PGPASSWORD=$(grep DB_PASSWORD .env | cut -d'=' -f2) pg_dump \
        -h localhost \
        -U "$DB_USER" \
        -d "$DB_NAME" \
        --no-owner \
        --no-privileges \
        -f "$BACKUP_PATH/database_$TIMESTAMP.sql"
    
    gzip "$BACKUP_PATH/database_$TIMESTAMP.sql"
}

# Function to create files backup
backup_files() {
    log "Creating files backup..."
    
    # Backup storage directory (uploaded files, logs, etc.)
    tar -czf "$BACKUP_PATH/storage_$TIMESTAMP.tar.gz" \
        -C "$APP_PATH" \
        --exclude='storage/framework/cache/*' \
        --exclude='storage/framework/sessions/*' \
        --exclude='storage/framework/views/*' \
        storage/
    
    # Backup .env file
    cp .env "$BACKUP_PATH/env_$TIMESTAMP"
}

# Function to create full backup
backup_full() {
    log "Creating full application backup..."
    
    tar -czf "$BACKUP_PATH/full_$TIMESTAMP.tar.gz" \
        -C "$(dirname $APP_PATH)" \
        --exclude='next-gold/node_modules' \
        --exclude='next-gold/storage/framework/cache/*' \
        --exclude='next-gold/storage/framework/sessions/*' \
        --exclude='next-gold/storage/framework/views/*' \
        --exclude='next-gold/.git' \
        next-gold/
}

# Function to clean old backups
cleanup_old_backups() {
    log "Cleaning up old backups..."
    
    find "$BACKUP_PATH" -name "database_*.sql.gz" -mtime +$RETENTION_DAYS -delete
    find "$BACKUP_PATH" -name "storage_*.tar.gz" -mtime +$RETENTION_DAYS -delete
    find "$BACKUP_PATH" -name "full_*.tar.gz" -mtime +$RETENTION_DAYS -delete
    find "$BACKUP_PATH" -name "env_*" -mtime +$RETENTION_DAYS -delete
}

# Function to verify backup
verify_backup() {
    local backup_file=$1
    log "Verifying backup: $backup_file"
    
    if [[ -f "$backup_file" ]]; then
        if [[ "$backup_file" == *.tar.gz ]]; then
            if tar -tzf "$backup_file" >/dev/null 2>&1; then
                log "✓ Backup verification successful"
                return 0
            fi
        elif [[ "$backup_file" == *.sql.gz ]]; then
            if gzip -t "$backup_file" >/dev/null 2>&1; then
                log "✓ Database backup verification successful"
                return 0
            fi
        fi
    fi
    
    error "✗ Backup verification failed"
    return 1
}

# Main backup function
main() {
    local backup_type=${1:-full}
    
    log "Starting $backup_type backup..."
    
    case $backup_type in
        "database")
            backup_database
            verify_backup "$BACKUP_PATH/database_$TIMESTAMP.sql.gz"
            ;;
        "files")
            backup_files
            verify_backup "$BACKUP_PATH/storage_$TIMESTAMP.tar.gz"
            ;;
        "full")
            backup_database
            backup_files
            backup_full
            verify_backup "$BACKUP_PATH/full_$TIMESTAMP.tar.gz"
            ;;
        *)
            error "Invalid backup type. Use: database, files, or full"
            exit 1
            ;;
    esac
    
    cleanup_old_backups
    
    # Show backup size
    total_size=$(du -sh "$BACKUP_PATH" | cut -f1)
    log "Backup completed. Total backup size: $total_size"
    
    # Send notification (if configured)
    if command -v mail >/dev/null 2>&1 && [[ -n "${BACKUP_EMAIL:-}" ]]; then
        echo "Next Gold backup completed successfully at $(date)" | \
            mail -s "Next Gold Backup - $backup_type" "$BACKUP_EMAIL"
    fi
}

# Run main function
main "$@"
