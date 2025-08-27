#!/bin/bash

# Next Gold Monitoring Script
# Monitors system health and sends alerts

# Configuration
APP_DIR="/var/www/next-gold"
LOG_FILE="/var/log/next-gold/monitor.log"
ALERT_EMAIL="${ALERT_EMAIL:-admin@localhost}"
THRESHOLD_CPU=80
THRESHOLD_MEMORY=80
THRESHOLD_DISK=90

# Create log directory
mkdir -p /var/log/next-gold

# Function to log messages
log() {
    echo "$(date +"%Y-%m-%d %H:%M:%S") - $1" | tee -a "$LOG_FILE"
}

# Function to send alert
send_alert() {
    local subject="$1"
    local message="$2"

    if command -v mail &> /dev/null; then
        echo "$message" | mail -s "$subject" "$ALERT_EMAIL"
    fi

    log "ALERT: $subject - $message"
}

# Check CPU usage
check_cpu() {
    local cpu_usage=$(top -bn1 | grep "Cpu(s)" | sed "s/.*, *\([0-9.]*\)%* id.*/\1/" | awk '{print 100 - $1}')

    if (( $(echo "$cpu_usage > $THRESHOLD_CPU" | bc -l) )); then
        send_alert "High CPU Usage Alert" "CPU usage is at ${cpu_usage}% (threshold: ${THRESHOLD_CPU}%)"
    fi

    echo "CPU Usage: ${cpu_usage}%"
}

# Check memory usage
check_memory() {
    local mem_usage=$(free | grep Mem | awk '{printf "%.0f", $3/$2 * 100.0}')

    if (( mem_usage > THRESHOLD_MEMORY )); then
        send_alert "High Memory Usage Alert" "Memory usage is at ${mem_usage}% (threshold: ${THRESHOLD_MEMORY}%)"
    fi

    echo "Memory Usage: ${mem_usage}%"
}

# Check disk usage
check_disk() {
    local disk_usage=$(df / | tail -1 | awk '{print $5}' | sed 's/%//')

    if (( disk_usage > THRESHOLD_DISK )); then
        send_alert "High Disk Usage Alert" "Disk usage is at ${disk_usage}% (threshold: ${THRESHOLD_DISK}%)"
    fi

    echo "Disk Usage: ${disk_usage}%"
}

# Check services
check_services() {
    local services=("nginx" "next-gold-queue" "redis-server" "postgresql")
    local failed_services=()

    for service in "${services[@]}"; do
        if ! systemctl is-active --quiet "$service"; then
            failed_services+=("$service")
        fi
    done

    if [ ${#failed_services[@]} -gt 0 ]; then
        send_alert "Service Failure Alert" "The following services are not running: ${failed_services[*]}"
    fi

    echo "Services Status: $((${#services[@]} - ${#failed_services[@]}))/${#services[@]} running"
}

# Check application health
check_application() {
    local app_url="http://localhost"
    local response_code=$(curl -s -o /dev/null -w "%{http_code}" "$app_url")

    if [ "$response_code" != "200" ]; then
        send_alert "Application Health Alert" "Application is not responding (HTTP $response_code)"
    fi

    echo "Application Status: HTTP $response_code"
}

# Check database connection
check_database() {
    cd "$APP_DIR"

    if ! php artisan tinker --execute="echo 'Database connection OK';" &> /dev/null; then
        send_alert "Database Connection Alert" "Cannot connect to database"
        echo "Database Status: FAILED"
    else
        echo "Database Status: OK"
    fi
}

# Check Redis connection
check_redis() {
    if ! redis-cli ping &> /dev/null; then
        send_alert "Redis Connection Alert" "Cannot connect to Redis"
        echo "Redis Status: FAILED"
    else
        echo "Redis Status: OK"
    fi
}

# Check queue status
check_queue() {
    cd "$APP_DIR"
    local failed_jobs=$(php artisan queue:failed | grep -c "failed_jobs" || echo "0")

    if (( failed_jobs > 0 )); then
        send_alert "Queue Alert" "There are $failed_jobs failed queue jobs"
    fi

    echo "Failed Queue Jobs: $failed_jobs"
}

# Main monitoring function
main() {
    log "=== System Monitoring Check ==="

    echo "=== System Resources ==="
    check_cpu
    check_memory
    check_disk
    echo

    echo "=== Services Status ==="
    check_services
    echo

    echo "=== Application Health ==="
    check_application
    check_database
    check_redis
    check_queue
    echo

    log "Monitoring check completed"
}

# Run monitoring
main
