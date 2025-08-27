# Next Gold Nginx Configuration Template
# Replace __SERVER_NAME__, __ROOT__, __PHP_VERSION__ with actual values

server {
    listen 80;
    server_name __SERVER_NAME__;
    root __ROOT__/public;
    index index.php;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_proxied expired no-cache no-store private must-revalidate auth;
    gzip_types text/plain text/css text/xml text/javascript application/x-javascript application/xml+rss application/javascript;

    # Client max body size
    client_max_body_size 100M;

    # Logs
    access_log /var/log/nginx/next-gold_access.log;
    error_log /var/log/nginx/next-gold_error.log;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php__PHP_VERSION__-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Cache static assets
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    # Security: Don't serve dotfiles
    location ~ /\. {
        deny all;
    }
}

# SSL Configuration (uncomment and configure if using SSL)
# server {
#     listen 443 ssl http2;
#     server_name __SERVER_NAME__;
#     root __ROOT__/public;
#
#     ssl_certificate /etc/letsencrypt/live/__SERVER_NAME__/fullchain.pem;
#     ssl_certificate_key /etc/letsencrypt/live/__SERVER_NAME__/privkey.pem;
#
#     # SSL Security
#     ssl_protocols TLSv1.2 TLSv1.3;
#     ssl_ciphers ECDHE-RSA-AES128-GCM-SHA256:ECDHE-RSA-AES256-GCM-SHA384:ECDHE-RSA-AES128-SHA256:ECDHE-RSA-AES256-SHA384;
#     ssl_prefer_server_ciphers off;
#
#     # Include all the configuration from the non-SSL server block above
#     include /etc/nginx/sites-available/next-gold;
# }
