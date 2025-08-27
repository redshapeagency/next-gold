#!/bin/bash

# Next Gold Health Check Script
# This script checks the health of all Next Gold components

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

APP_PATH="/var/www/next-gold"
DOMAIN="${1:-localhost}"

echo -e "${BLUE}Next Gold Health Check${NC}"
echo "=========================="

# Check if application directory exists
if [[ ! -d "$APP_PATH" ]]; then
    echo -e "${RED}✗ Application directory not found: $APP_PATH${NC}"
    exit 1
fi

cd "$APP_PATH"

# Check PHP version
echo -n "PHP Version: "
php_version=$(php -v | head -n1 | cut -d' ' -f2)
if [[ $php_version == 8.4* ]]; then
    echo -e "${GREEN}✓ $php_version${NC}"
else
    echo -e "${YELLOW}⚠ $php_version (expected 8.4.x)${NC}"
fi

# Check Composer dependencies
echo -n "Composer Dependencies: "
if composer check-platform-reqs --no-interaction &>/dev/null; then
    echo -e "${GREEN}✓ OK${NC}"
else
    echo -e "${RED}✗ Missing dependencies${NC}"
fi

# Check database connection
echo -n "Database Connection: "
if php artisan migrate:status &>/dev/null; then
    echo -e "${GREEN}✓ Connected${NC}"
else
    echo -e "${RED}✗ Connection failed${NC}"
fi

# Check Redis connection
echo -n "Redis Connection: "
if php artisan tinker --execute="Redis::ping()" &>/dev/null; then
    echo -e "${GREEN}✓ Connected${NC}"
else
    echo -e "${RED}✗ Connection failed${NC}"
fi

# Check file permissions
echo -n "File Permissions: "
if [[ -w "storage" && -w "bootstrap/cache" ]]; then
    echo -e "${GREEN}✓ OK${NC}"
else
    echo -e "${RED}✗ Permission issues${NC}"
fi

# Check queue worker
echo -n "Queue Worker: "
if systemctl is-active --quiet next-gold-worker; then
    echo -e "${GREEN}✓ Running${NC}"
else
    echo -e "${RED}✗ Not running${NC}"
fi

# Check web server
echo -n "Web Server: "
if systemctl is-active --quiet nginx; then
    echo -e "${GREEN}✓ Nginx running${NC}"
else
    echo -e "${RED}✗ Nginx not running${NC}"
fi

# Check application response
echo -n "Application Response: "
status_code=$(curl -s -o /dev/null -w "%{http_code}" "http://$DOMAIN" || echo "000")
if [[ $status_code == "200" ]]; then
    echo -e "${GREEN}✓ HTTP 200${NC}"
elif [[ $status_code == "000" ]]; then
    echo -e "${RED}✗ No response${NC}"
else
    echo -e "${YELLOW}⚠ HTTP $status_code${NC}"
fi

# Check disk space
echo -n "Disk Space: "
disk_usage=$(df -h "$APP_PATH" | awk 'NR==2 {print $5}' | sed 's/%//')
if [[ $disk_usage -lt 80 ]]; then
    echo -e "${GREEN}✓ ${disk_usage}% used${NC}"
elif [[ $disk_usage -lt 90 ]]; then
    echo -e "${YELLOW}⚠ ${disk_usage}% used${NC}"
else
    echo -e "${RED}✗ ${disk_usage}% used${NC}"
fi

# Check memory usage
echo -n "Memory Usage: "
memory_usage=$(free | awk 'NR==2 {printf "%.1f", $3*100/$2}')
if (( $(echo "$memory_usage < 80" | bc -l) )); then
    echo -e "${GREEN}✓ ${memory_usage}% used${NC}"
elif (( $(echo "$memory_usage < 90" | bc -l) )); then
    echo -e "${YELLOW}⚠ ${memory_usage}% used${NC}"
else
    echo -e "${RED}✗ ${memory_usage}% used${NC}"
fi

# Check log file sizes
echo -n "Log Files: "
log_size=$(du -sh storage/logs 2>/dev/null | cut -f1)
echo -e "${BLUE}ℹ Total size: $log_size${NC}"

# Check SSL certificate (if HTTPS)
if [[ $DOMAIN != "localhost" ]]; then
    echo -n "SSL Certificate: "
    ssl_expiry=$(echo | openssl s_client -servername "$DOMAIN" -connect "$DOMAIN:443" 2>/dev/null | openssl x509 -noout -dates 2>/dev/null | grep notAfter | cut -d= -f2)
    if [[ -n $ssl_expiry ]]; then
        expiry_date=$(date -d "$ssl_expiry" +%s)
        current_date=$(date +%s)
        days_left=$(( (expiry_date - current_date) / 86400 ))
        
        if [[ $days_left -gt 30 ]]; then
            echo -e "${GREEN}✓ Valid for $days_left days${NC}"
        elif [[ $days_left -gt 7 ]]; then
            echo -e "${YELLOW}⚠ Expires in $days_left days${NC}"
        else
            echo -e "${RED}✗ Expires in $days_left days${NC}"
        fi
    else
        echo -e "${YELLOW}⚠ No SSL or connection failed${NC}"
    fi
fi

echo
echo -e "${BLUE}System Information:${NC}"
echo "- OS: $(lsb_release -d | cut -f2)"
echo "- Uptime: $(uptime -p)"
echo "- Load: $(uptime | awk -F'load average:' '{print $2}')"

echo
echo -e "${BLUE}Quick Commands:${NC}"
echo "- Restart queue worker: sudo systemctl restart next-gold-worker"
echo "- View logs: tail -f storage/logs/laravel.log"
echo "- Clear cache: php artisan cache:clear"
echo "- Check queue: php artisan queue:work --once"
