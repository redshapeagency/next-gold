#!/bin/bash

# Next Gold Update Script
# Safely updates the application to the latest version

set -e

# Configuration
APP_PATH="/var/www/next-gold"
BACKUP_PATH="/var/backups/next-gold"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

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

# Check if running as correct user
if [[ $EUID -eq 0 ]]; then
    error "This script should not be run as root"
    exit 1
fi

# Check if application exists
if [[ ! -d "$APP_PATH" ]]; then
    error "Application not found at $APP_PATH"
    exit 1
fi

cd "$APP_PATH"

# Function to create pre-update backup
create_backup() {
    log "Creating pre-update backup..."
    
    mkdir -p "$BACKUP_PATH"
    
    # Create database backup
    DB_NAME=$(grep DB_DATABASE .env | cut -d'=' -f2)
    DB_USER=$(grep DB_USERNAME .env | cut -d'=' -f2)
    
    PGPASSWORD=$(grep DB_PASSWORD .env | cut -d'=' -f2) pg_dump \
        -h localhost \
        -U "$DB_USER" \
        -d "$DB_NAME" \
        --no-owner \
        --no-privileges \
        -f "$BACKUP_PATH/pre_update_db_$TIMESTAMP.sql"
    
    gzip "$BACKUP_PATH/pre_update_db_$TIMESTAMP.sql"
    
    # Backup current application
    tar -czf "$BACKUP_PATH/pre_update_app_$TIMESTAMP.tar.gz" \
        -C "$(dirname $APP_PATH)" \
        --exclude='next-gold/node_modules' \
        --exclude='next-gold/.git' \
        next-gold/
    
    log "✓ Backup created successfully"
}

# Function to enable maintenance mode
enable_maintenance() {
    log "Enabling maintenance mode..."
    php artisan down --refresh=15
}

# Function to disable maintenance mode
disable_maintenance() {
    log "Disabling maintenance mode..."
    php artisan up
}

# Function to update git repository
update_git() {
    log "Updating from git repository..."
    
    # Stash any local changes
    git stash push -m "Pre-update stash $TIMESTAMP"
    
    # Fetch latest changes
    git fetch origin
    
    # Checkout to latest release or main branch
    local branch=${1:-main}
    git checkout "$branch"
    git pull origin "$branch"
    
    log "✓ Git repository updated"
}

# Function to update dependencies
update_dependencies() {
    log "Updating dependencies..."
    
    # Update Composer dependencies
    composer install --no-dev --optimize-autoloader --no-interaction
    
    # Update Node.js dependencies
    npm ci --only=production
    npm run build
    
    log "✓ Dependencies updated"
}

# Function to run database migrations
run_migrations() {
    log "Running database migrations..."
    
    php artisan migrate --force
    
    log "✓ Database migrations completed"
}

# Function to clear and rebuild cache
rebuild_cache() {
    log "Rebuilding application cache..."
    
    # Clear all caches
    php artisan cache:clear
    php artisan config:clear
    php artisan route:clear
    php artisan view:clear
    
    # Rebuild caches
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    
    # Clear opcache if available
    if command -v php-fpm >/dev/null 2>&1; then
        sudo systemctl reload php8.4-fpm
    fi
    
    log "✓ Cache rebuilt"
}

# Function to restart services
restart_services() {
    log "Restarting services..."
    
    # Restart queue worker
    sudo systemctl restart next-gold-worker
    
    # Reload nginx
    sudo systemctl reload nginx
    
    log "✓ Services restarted"
}

# Function to run health check
health_check() {
    log "Running health check..."
    
    if [[ -f "scripts/health-check.sh" ]]; then
        bash scripts/health-check.sh
    else
        # Basic health checks
        if php artisan inspire >/dev/null 2>&1; then
            log "✓ Application is responding"
        else
            error "✗ Application health check failed"
            return 1
        fi
    fi
}

# Function to rollback update
rollback() {
    error "Update failed. Rolling back..."
    
    # Restore database
    local db_backup="$BACKUP_PATH/pre_update_db_$TIMESTAMP.sql.gz"
    if [[ -f "$db_backup" ]]; then
        log "Restoring database..."
        
        DB_NAME=$(grep DB_DATABASE .env | cut -d'=' -f2)
        DB_USER=$(grep DB_USERNAME .env | cut -d'=' -f2)
        
        gunzip -c "$db_backup" | PGPASSWORD=$(grep DB_PASSWORD .env | cut -d'=' -f2) \
            psql -h localhost -U "$DB_USER" -d "$DB_NAME"
    fi
    
    # Restore application files
    local app_backup="$BACKUP_PATH/pre_update_app_$TIMESTAMP.tar.gz"
    if [[ -f "$app_backup" ]]; then
        log "Restoring application files..."
        
        cd "$(dirname $APP_PATH)"
        rm -rf next-gold
        tar -xzf "$app_backup"
    fi
    
    disable_maintenance
    restart_services
    
    error "Rollback completed"
    exit 1
}

# Main update function
main() {
    local branch=${1:-main}
    local skip_backup=${2:-false}
    
    log "Starting Next Gold update..."
    
    # Trap to handle errors
    trap rollback ERR
    
    # Create backup unless skipped
    if [[ "$skip_backup" != "true" ]]; then
        create_backup
    fi
    
    enable_maintenance
    
    update_git "$branch"
    update_dependencies
    run_migrations
    rebuild_cache
    
    # Run any post-update commands
    if [[ -f "scripts/post-update.sh" ]]; then
        log "Running post-update script..."
        bash scripts/post-update.sh
    fi
    
    restart_services
    disable_maintenance
    
    # Give application time to start
    sleep 5
    
    # Health check
    if ! health_check; then
        rollback
    fi
    
    log "✓ Update completed successfully!"
    
    # Show version info
    if command -v git >/dev/null 2>&1; then
        info "Current version: $(git describe --tags --always)"
        info "Last commit: $(git log -1 --pretty=format:'%h - %s (%cr)')"
    fi
    
    # Clean up old backups (keep last 5)
    find "$BACKUP_PATH" -name "pre_update_*" -type f | sort -r | tail -n +6 | xargs rm -f
    
    # Remove error trap
    trap - ERR
}

# Show usage
usage() {
    echo "Usage: $0 [branch] [skip_backup]"
    echo
    echo "Options:"
    echo "  branch       Git branch to update to (default: main)"
    echo "  skip_backup  Skip pre-update backup (true/false, default: false)"
    echo
    echo "Examples:"
    echo "  $0                    # Update to main branch with backup"
    echo "  $0 develop           # Update to develop branch with backup"
    echo "  $0 main true         # Update to main branch without backup"
}

# Handle command line arguments
case ${1:-} in
    -h|--help)
        usage
        exit 0
        ;;
    *)
        main "$@"
        ;;
esac
