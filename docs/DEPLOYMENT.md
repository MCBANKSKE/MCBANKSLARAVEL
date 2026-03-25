# MCBANKS Laravel Production Deployment Guide

## Overview

This guide provides comprehensive instructions for deploying the MCBANKS Laravel application to production environments. It covers various deployment strategies, server configuration, security hardening, and ongoing maintenance.

## Deployment Strategies

### 1. Traditional Server Deployment

**Best for:** Dedicated servers, VPS, or cloud instances

### 2. Container Deployment (Docker)

**Best for:** Scalable, reproducible deployments

### 3. Platform as a Service (PaaS)

**Best for:** Quick deployments, managed infrastructure

### 4. Serverless Deployment

**Best for:** Event-driven, pay-per-use applications

---

## 1. Traditional Server Deployment

### Server Requirements

#### Minimum Specifications

- **CPU**: 2 cores
- **RAM**: 4GB
- **Storage**: 50GB SSD
- **OS**: Ubuntu 20.04+ / CentOS 8+ / Debian 10+

#### Recommended Specifications

- **CPU**: 4 cores
- **RAM**: 8GB
- **Storage**: 100GB SSD
- **OS**: Ubuntu 22.04 LTS

### Server Setup

#### 1. Initial Server Configuration

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Create deployment user
sudo adduser deploy
sudo usermod -aG sudo deploy

# Configure firewall
sudo ufw allow OpenSSH
sudo ufw allow 'Nginx Full'
sudo ufw enable
```

#### 2. Install Required Software

```bash
# Install Nginx
sudo apt install nginx -y

# Install PHP 8.2 and extensions
sudo apt install php8.2 php8.2-fpm php8.2-mysql php8.2-xml php8.2-mbstring php8.2-curl php8.2-zip php8.2-gd php8.2-bcmath php8.2-intl php8.2-soap php8.2-imap -y

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer

# Install Node.js
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install -y nodejs

# Install Supervisor
sudo apt install supervisor -y

# Install Redis (for caching and queues)
sudo apt install redis-server -y
```

#### 3. Database Setup

```bash
# Install MySQL
sudo apt install mysql-server -y

# Secure MySQL
sudo mysql_secure_installation

# Create database and user
sudo mysql -e "
CREATE DATABASE mcbankslaravel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'mcbanks'@'localhost' IDENTIFIED BY 'strong_password_here';
GRANT ALL PRIVILEGES ON mcbankslaravel.* TO 'mcbanks'@'localhost';
FLUSH PRIVILEGES;
"
```

#### 4. PHP Configuration

Edit `/etc/php/8.2/fpm/php.ini`:

```ini
; Production settings
memory_limit = 256M
max_execution_time = 300
max_input_vars = 3000
upload_max_filesize = 10M
post_max_size = 10M
realpath_cache_size = 4096K
realpath_cache_ttl = 600

; Security settings
expose_php = Off
display_errors = Off
log_errors = On
error_log = /var/log/php_errors.log
```

#### 5. Nginx Configuration

Create `/etc/nginx/sites-available/mcbankslaravel`:

```nginx
server {
    listen 80;
    server_name your-domain.com www.your-domain.com;
    root /var/www/mcbankslaravel/public;
    index index.php index.html index.htm;

    # Redirect to HTTPS
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name your-domain.com www.your-domain.com;
    root /var/www/mcbankslaravel/public;
    index index.php index.html index.htm;

    # SSL Configuration
    ssl_certificate /etc/letsencrypt/live/your-domain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/your-domain.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512:ECDHE-RSA-AES256-GCM-SHA384:DHE-RSA-AES256-GCM-SHA384;
    ssl_prefer_server_ciphers off;
    ssl_session_cache shared:SSL:10m;
    ssl_session_timeout 10m;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;

    # File sizes
    client_max_body_size 10M;

    # Laravel specific
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    # Static files caching
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        access_log off;
    }

    # Deny access to sensitive files
    location ~ /\. {
        deny all;
    }

    location ~ /storage/app/.*\.php$ {
        deny all;
    }

    location ~ /storage/framework/.*\.php$ {
        deny all;
    }

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_proxied any;
    gzip_comp_level 6;
    gzip_types
        text/plain
        text/css
        text/xml
        text/javascript
        application/json
        application/javascript
        application/xml+rss
        application/atom+xml
        image/svg+xml;
}
```

Enable the site:

```bash
sudo ln -s /etc/nginx/sites-available/mcbankslaravel /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

#### 6. SSL Certificate (Let's Encrypt)

```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx -y

# Obtain SSL certificate
sudo certbot --nginx -d your-domain.com -d www.your-domain.com

# Set up auto-renewal
sudo crontab -e
# Add this line:
0 12 * * * /usr/bin/certbot renew --quiet
```

### Application Deployment

#### 1. Clone Repository

```bash
# Create project directory
sudo mkdir -p /var/www/mcbankslaravel
sudo chown deploy:deploy /var/www/mcbankslaravel

# Clone repository
cd /var/www/mcbankslaravel
git clone https://github.com/MCBANKSKE/MCBANKSLARAVEL.git .
```

#### 2. Install Dependencies

```bash
# Install PHP dependencies
composer install --optimize-autoloader --no-dev

# Install Node.js dependencies
npm install --production
```

#### 3. Environment Configuration

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Edit environment file
nano .env
```

Production `.env` configuration:

```env
APP_NAME="MCBANKS LARAVEL"
APP_ENV=production
APP_KEY=base64:your_generated_key_here
APP_DEBUG=false
APP_URL=https://your-domain.com

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mcbankslaravel
DB_USERNAME=mcbanks
DB_PASSWORD=strong_password_here

CACHE_DRIVER=redis
FILESYSTEM_DISK=local
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
SESSION_LIFETIME=120

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=your-smtp-server.com
MAIL_PORT=587
MAIL_USERNAME=your_email@domain.com
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="${APP_NAME}@your-domain.com"
MAIL_FROM_NAME="${APP_NAME}"

# Social Authentication
GOOGLE_CLIENT_ID=your_google_client_id
GOOGLE_CLIENT_SECRET=your_google_client_secret
GOOGLE_REDIRECT_URI="${APP_URL}/auth/google/callback"

GITHUB_CLIENT_ID=your_github_client_id
GITHUB_CLIENT_SECRET=your_github_client_secret
GITHUB_REDIRECT_URI="${APP_URL}/auth/github/callback"

# Admin User
ADMIN_EMAIL=admin@your-domain.com
ADMIN_PASSWORD=strong_admin_password
ADMIN_NAME="Admin User"

# Security
BCRYPT_ROUNDS=12
```

#### 4. Optimize Application

```bash
# Run migrations
php artisan migrate --force

# Seed database (if needed)
php artisan db:seed --force

# Clear and cache configurations
php artisan config:clear
php artisan config:cache

php artisan route:clear
php artisan route:cache

php artisan view:clear
php artisan view:cache

# Optimize autoloader
composer dump-autoload --optimize

# Build frontend assets
npm run build

# Create storage link
php artisan storage:link

# Set permissions
sudo chown -R www-data:www-data /var/www/mcbankslaravel/storage
sudo chown -R www-data:www-data /var/www/mcbankslaravel/bootstrap/cache
sudo chmod -R 775 /var/www/mcbankslaravel/storage
sudo chmod -R 775 /var/www/mcbankslaravel/bootstrap/cache
```

### Queue Worker Setup

#### 1. Supervisor Configuration

Create `/etc/supervisor/conf.d/mcbankslaravel-worker.conf`:

```ini
[program:mcbankslaravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/mcbankslaravel/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=deploy
numprocs=4
redirect_stderr=true
stdout_logfile=/var/www/mcbankslaravel/storage/logs/worker.log
stopwaitsecs=3600
```

Start the worker:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start mcbankslaravel-worker:*
```

### Monitoring & Logging

#### 1. Log Rotation

Create `/etc/logrotate.d/mcbankslaravel`:

```
/var/www/mcbankslaravel/storage/logs/*.log {
    daily
    missingok
    rotate 52
    compress
    delaycompress
    notifempty
    create 644 www-data www-data
    postrotate
        systemctl reload php8.2-fpm
    endscript
}
```

#### 2. Monitoring Setup

```bash
# Install monitoring tools
sudo apt install htop iotop nethogs -y

# Set up basic monitoring script
sudo nano /usr/local/bin/mcbankslaravel-monitor.sh
```

```bash
#!/bin/bash
# Basic monitoring script

# Check if Nginx is running
if ! systemctl is-active --quiet nginx; then
    echo "Nginx is not running. Restarting..."
    sudo systemctl restart nginx
fi

# Check if PHP-FPM is running
if ! systemctl is-active --quiet php8.2-fpm; then
    echo "PHP-FPM is not running. Restarting..."
    sudo systemctl restart php8.2-fpm
fi

# Check if Redis is running
if ! systemctl is-active --quiet redis-server; then
    echo "Redis is not running. Restarting..."
    sudo systemctl restart redis-server
fi

# Check disk space
DISK_USAGE=$(df /var/www/mcbankslaravel | awk 'NR==2 {print $5}' | sed 's/%//')
if [ $DISK_USAGE -gt 80 ]; then
    echo "Warning: Disk usage is ${DISK_USAGE}%"
fi

# Check memory usage
MEM_USAGE=$(free | awk 'NR==2{printf "%.0f", $3*100/$2}')
if [ $MEM_USAGE -gt 80 ]; then
    echo "Warning: Memory usage is ${MEM_USAGE}%"
fi
```

```bash
sudo chmod +x /usr/local/bin/mcbankslaravel-monitor.sh
sudo crontab -e
# Add this line:
*/5 * * * * /usr/local/bin/mcbankslaravel-monitor.sh
```

---

## 2. Docker Deployment

### Dockerfile

Create `Dockerfile`:

```dockerfile
# Use PHP 8.2 FPM as base image
FROM php:8.2-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    oniguruma-dev \
    libxml2-dev \
    zip \
    unzip \
    nodejs \
    npm \
    nginx \
    supervisor

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . /var/www/html

# Install PHP dependencies
RUN composer install --optimize-autoloader --no-dev

# Install Node.js dependencies and build assets
RUN npm install --production
RUN npm run build

# Create storage link
RUN php artisan storage:link

# Copy Nginx configuration
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/default.conf /etc/nginx/conf.d/default.conf

# Copy Supervisor configuration
COPY docker/supervisor.conf /etc/supervisor/conf.d/supervisor.conf

# Set permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Expose port
EXPOSE 80

# Start Supervisor
CMD ["supervisord", "-c", "/etc/supervisor/conf.d/supervisor.conf"]
```

### Docker Compose

Create `docker-compose.yml`:

```yaml
version: '3.8'

services:
  app:
    build: .
    container_name: mcbankslaravel-app
    restart: unless-stopped
    working_dir: /var/www/html
    volumes:
      - ./:/var/www/html
      - ./storage/app/public:/var/www/html/public/storage
    networks:
      - mcbankslaravel-network
    depends_on:
      - mysql
      - redis

  nginx:
    image: nginx:alpine
    container_name: mcbankslaravel-nginx
    restart: unless-stopped
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./:/var/www/html
      - ./docker/nginx/default.conf:/etc/nginx/conf.d/default.conf
      - ./docker/ssl:/etc/nginx/ssl
    networks:
      - mcbankslaravel-network
    depends_on:
      - app

  mysql:
    image: mysql:8.0
    container_name: mcbankslaravel-mysql
    restart: unless-stopped
    environment:
      MYSQL_DATABASE: mcbankslaravel
      MYSQL_USER: mcbanks
      MYSQL_PASSWORD: strong_password_here
      MYSQL_ROOT_PASSWORD: very_strong_root_password
    volumes:
      - mysql_data:/var/lib/mysql
    ports:
      - "3306:3306"
    networks:
      - mcbankslaravel-network

  redis:
    image: redis:7-alpine
    container_name: mcbankslaravel-redis
    restart: unless-stopped
    ports:
      - "6379:6379"
    volumes:
      - redis_data:/data
    networks:
      - mcbankslaravel-network

  queue:
    build: .
    container_name: mcbankslaravel-queue
    restart: unless-stopped
    command: php artisan queue:work --sleep=3 --tries=3 --max-time=3600
    volumes:
      - ./:/var/www/html
    networks:
      - mcbankslaravel-network
    depends_on:
      - mysql
      - redis

networks:
  mcbankslaravel-network:
    driver: bridge

volumes:
  mysql_data:
    driver: local
  redis_data:
    driver: local
```

### Docker Configuration Files

Create `docker/nginx/default.conf`:

```nginx
server {
    listen 80;
    server_name localhost;
    root /var/www/html/public;
    index index.php index.html;

    client_max_body_size 10M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass app:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\. {
        deny all;
    }
}
```

Create `docker/supervisor.conf`:

```ini
[supervisord]
nodaemon=true
user=root

[program:php-fpm]
command=php-fpm
autostart=true
autorestart=true
priority=5

[program:nginx]
command=nginx -g "daemon off;"
autostart=true
autorestart=true
priority=10
```

### Docker Deployment Commands

```bash
# Build and start containers
docker-compose up -d --build

# Run migrations
docker-compose exec app php artisan migrate --force

# Seed database
docker-compose exec app php artisan db:seed --force

# Optimize application
docker-compose exec app php artisan optimize

# View logs
docker-compose logs -f app

# Scale workers
docker-compose up -d --scale queue=3
```

---

## 3. PaaS Deployment (DigitalOcean App Platform)

### app.yaml Configuration

```yaml
name: mcbankslaravel
services:
- name: web
  source_dir: /
  github:
    repo: MCBANKSKE/MCBANKSLARAVEL
    branch: main
  run_command: "php artisan serve --host=0.0.0.0 --port=8080"
  environment_slug: php
  instance_count: 1
  instance_size_slug: basic-xxs
  envs:
  - key: APP_ENV
    value: production
  - key: APP_DEBUG
    value: "false"
  - key: APP_URL
    value: ${_self.URL}
  - key: DB_HOST
    value: ${db.HOSTNAME}
  - key: DB_DATABASE
    value: ${db.DATABASE}
  - key: DB_USERNAME
    value: ${db.USERNAME}
  - key: DB_PASSWORD
    value: ${db.PASSWORD}
  - key: CACHE_DRIVER
    value: redis
  - key: REDIS_HOST
    value: ${redis.HOSTNAME}
  - key: REDIS_PASSWORD
    value: ${redis.PASSWORD}
  - key: QUEUE_CONNECTION
    value: redis
  - key: SESSION_DRIVER
    value: redis

databases:
- name: db
  engine: MySQL
  version: "8"

- name: redis
  engine: Redis
  version: "7"
```

### Deployment Steps

```bash
# Install doctl
curl -sSL https://github.com/digitalocean/doctl/releases/latest/download/doctl-linux-amd64.tar.gz | tar xz
sudo mv doctl /usr/local/bin/

# Authenticate
doctl auth init

# Deploy
doctl apps create --spec app.yaml
```

---

## 4. CI/CD Pipeline (GitHub Actions)

### GitHub Actions Workflow

Create `.github/workflows/deploy.yml`:

```yaml
name: Deploy to Production

on:
  push:
    branches: [ main ]

jobs:
  test:
    runs-on: ubuntu-latest
    
    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ROOT_PASSWORD: password
          MYSQL_DATABASE: mcbankslaravel
        options: >-
          --health-cmd="mysqladmin ping"
          --health-interval=10s
          --health-timeout=5s
          --health-retries=3
        ports:
          - 3306:3306

    steps:
    - uses: actions/checkout@v3
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.2'
        extensions: mbstring, xml, bcmath, gd, pdo_mysql
        coverage: xdebug
    
    - name: Copy environment file
      run: cp .env.example .env
    
    - name: Install dependencies
      run: composer install -q --no-ansi --no-interaction --no-scripts --no-progress --prefer-dist
    
    - name: Generate application key
      run: php artisan key:generate
    
    - name: Run migrations
      run: php artisan migrate --force
      env:
        DB_CONNECTION: mysql
        DB_HOST: 127.0.0.1
        DB_DATABASE: mcbankslaravel
        DB_USERNAME: root
        DB_PASSWORD: password
    
    - name: Run tests
      run: php artisan test --coverage

  deploy:
    needs: test
    runs-on: ubuntu-latest
    if: github.ref == 'refs/heads/main'
    
    steps:
    - uses: actions/checkout@v3
    
    - name: Deploy to production
      uses: appleboy/ssh-action@v0.1.5
      with:
        host: ${{ secrets.HOST }}
        username: ${{ secrets.USERNAME }}
        key: ${{ secrets.SSH_KEY }}
        script: |
          cd /var/www/mcbankslaravel
          git pull origin main
          composer install --optimize-autoloader --no-dev
          npm install --production
          npm run build
          php artisan migrate --force
          php artisan config:cache
          php artisan route:cache
          php artisan view:cache
          php artisan queue:restart
          sudo systemctl reload nginx
          sudo systemctl reload php8.2-fpm
```

---

## Security Hardening

### 1. Server Security

```bash
# Disable root login
sudo nano /etc/ssh/sshd_config
# Set: PermitRootLogin no

# Configure fail2ban
sudo apt install fail2ban -y
sudo cp /etc/fail2ban/jail.conf /etc/fail2ban/jail.local
sudo nano /etc/fail2ban/jail.local

# Enable fail2ban
sudo systemctl enable fail2ban
sudo systemctl start fail2ban

# Set up automatic updates
sudo apt install unattended-upgrades -y
sudo dpkg-reconfigure -plow unattended-upgrades
```

### 2. Application Security

#### Environment Security

```bash
# Secure .env file
chmod 600 .env

# Prevent .env from being committed
echo ".env" >> .gitignore
```

#### File Permissions

```bash
# Set proper permissions
sudo chown -R www-data:www-data /var/www/mcbankslaravel
sudo find /var/www/mcbankslaravel -type f -exec chmod 644 {} \;
sudo find /var/www/mcbankslaravel -type d -exec chmod 755 {} \;
sudo chmod -R 775 /var/www/mcbankslaravel/storage
sudo chmod -R 775 /var/www/mcbankslaravel/bootstrap/cache
```

#### Security Headers

Add to Nginx configuration:

```nginx
# Security headers
add_header X-Frame-Options "SAMEORIGIN" always;
add_header X-XSS-Protection "1; mode=block" always;
add_header X-Content-Type-Options "nosniff" always;
add_header Referrer-Policy "no-referrer-when-downgrade" always;
add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;
add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
```

### 3. Database Security

```sql
-- Create limited database user for application
CREATE USER 'mcbanks_app'@'localhost' IDENTIFIED BY 'strong_password';
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, INDEX, DROP ON mcbankslaravel.* TO 'mcbanks_app'@'localhost';

-- Remove test database
DROP DATABASE IF EXISTS test;

-- Remove anonymous users
DELETE FROM mysql.user WHERE User='';

-- Remove remote root access
DELETE FROM mysql.user WHERE User='root' AND Host NOT IN ('localhost', '127.0.0.1', '::1');
FLUSH PRIVILEGES;
```

---

## Performance Optimization

### 1. PHP Optimization

```ini
; /etc/php/8.2/fpm/php.ini
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=4000
opcache.revalidate_freq=60
opcache.fast_shutdown=1
opcache.enable_cli=1

; /etc/php/8.2/fpm/pool.d/www.conf
pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
pm.max_requests = 500
```

### 2. Database Optimization

```sql
-- MySQL configuration
SET GLOBAL innodb_buffer_pool_size = 1073741824; -- 1GB
SET GLOBAL innodb_log_file_size = 268435456; -- 256MB
SET GLOBAL query_cache_size = 67108864; -- 64MB
SET GLOBAL query_cache_type = 1;
```

### 3. Caching Strategy

```bash
# Redis configuration
sudo nano /etc/redis/redis.conf

# Set memory limit
maxmemory 256mb
maxmemory-policy allkeys-lru

# Enable persistence
save 900 1
save 300 10
save 60 10000
```

### 4. CDN Setup

Configure CDN for static assets:

```nginx
# Add to Nginx config
location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
    expires 1y;
    add_header Cache-Control "public, immutable";
    add_header Access-Control-Allow-Origin "*";
    access_log off;
}
```

---

## Backup Strategy

### 1. Database Backup

Create backup script `/usr/local/bin/mcbankslaravel-backup.sh`:

```bash
#!/bin/bash

BACKUP_DIR="/var/backups/mcbankslaravel"
DATE=$(date +%Y%m%d_%H%M%S)
DB_NAME="mcbankslaravel"
DB_USER="mcbanks"
DB_PASS="strong_password_here"

# Create backup directory
mkdir -p $BACKUP_DIR

# Database backup
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/db_backup_$DATE.sql.gz

# File backup
tar -czf $BACKUP_DIR/files_backup_$DATE.tar.gz /var/www/mcbankslaravel/storage/app/public

# Remove old backups (keep 30 days)
find $BACKUP_DIR -name "*.gz" -mtime +30 -delete

# Upload to cloud storage (optional)
# aws s3 cp $BACKUP_DIR/db_backup_$DATE.sql.gz s3://your-backup-bucket/
```

```bash
# Make executable and schedule
sudo chmod +x /usr/local/bin/mcbankslaravel-backup.sh
sudo crontab -e
# Add: 0 2 * * * /usr/local/bin/mcbankslaravel-backup.sh
```

### 2. Application Backup

```bash
# Full application backup
#!/bin/bash

DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/var/backups/mcbankslaravel"

# Create backup
tar -czf $BACKUP_DIR/full_backup_$DATE.tar.gz \
    --exclude=vendor \
    --exclude=node_modules \
    --exclude=storage/logs \
    --exclude=storage/framework/cache \
    /var/www/mcbankslaravel
```

---

## Monitoring & Alerting

### 1. Application Monitoring

Install Laravel Telescope:

```bash
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate
```

### 2. Server Monitoring

```bash
# Install monitoring tools
sudo apt install htop iotop nethogs -y

# Set up log monitoring
sudo apt install logwatch -y
sudo nano /etc/logwatch/conf/logwatch.conf
```

### 3. Uptime Monitoring

Use external services like:
- Uptime Robot
- Pingdom
- StatusCake

### 4. Error Tracking

Integrate error tracking services:
- Sentry
- Bugsnag
- Rollbar

---

## Troubleshooting

### Common Issues

#### 1. 502 Bad Gateway

```bash
# Check PHP-FPM status
sudo systemctl status php8.2-fpm

# Restart PHP-FPM
sudo systemctl restart php8.2-fpm

# Check Nginx error log
sudo tail -f /var/log/nginx/error.log
```

#### 2. Database Connection Failed

```bash
# Check MySQL status
sudo systemctl status mysql

# Test connection
mysql -u mcbanks -p -h localhost mcbankslaravel

# Check MySQL error log
sudo tail -f /var/log/mysql/error.log
```

#### 3. Queue Not Processing

```bash
# Check Supervisor status
sudo supervisorctl status

# Restart queue workers
sudo supervisorctl restart mcbankslaravel-worker:*

# Check queue logs
tail -f /var/www/mcbankslaravel/storage/logs/worker.log
```

#### 4. High Memory Usage

```bash
# Check memory usage
free -h
htop

# Optimize PHP-FPM
sudo nano /etc/php/8.2/fpm/pool.d/www.conf

# Restart PHP-FPM
sudo systemctl restart php8.2-fpm
```

### Emergency Procedures

#### 1. Rollback Deployment

```bash
# Roll to previous commit
cd /var/www/mcbankslaravel
git log --oneline
git checkout <previous-commit-hash>

# Re-optimize
php artisan optimize
sudo systemctl reload nginx
```

#### 2. Database Recovery

```bash
# Restore from backup
gunzip < /var/backups/mcbankslaravel/db_backup_20260325_020000.sql.gz | mysql -u mcbanks -p mcbankslaravel
```

#### 3. Emergency Maintenance Mode

```bash
# Enable maintenance mode
php artisan down

# Disable maintenance mode
php artisan up
```

---

## Maintenance Schedule

### Daily Tasks

- Monitor server resources
- Check error logs
- Verify backups completed

### Weekly Tasks

- Update security patches
- Review performance metrics
- Clean up old logs

### Monthly Tasks

- Update dependencies
- Review security settings
- Test backup restoration

### Quarterly Tasks

- SSL certificate renewal check
- Security audit
- Performance optimization review

---

## Support & Resources

### Documentation

- [Laravel Documentation](https://laravel.com/docs)
- [Nginx Documentation](https://nginx.org/en/docs/)
- [MySQL Documentation](https://dev.mysql.com/doc/)
- [Redis Documentation](https://redis.io/documentation)

### Community

- [Laravel Forums](https://laracasts.com/discuss)
- [Stack Overflow](https://stackoverflow.com/questions/tagged/laravel)
- [GitHub Issues](https://github.com/MCBANKSKE/MCBANKSLARAVEL/issues)

### Emergency Contacts

- **DevOps Team**: devops@your-domain.com
- **Security Team**: security@your-domain.com
- **Support Team**: support@your-domain.com

---

This deployment guide covers all aspects of deploying MCBANKS Laravel to production. Choose the deployment strategy that best fits your infrastructure and requirements. Always test deployments in a staging environment before deploying to production.
