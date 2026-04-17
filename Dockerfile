# Dockerfile for CodeRunner Academy
# Builds a production-ready Moodle container with CodeRunner

FROM php:8.2-apache

ENV DEBIAN_FRONTEND=noninteractive \
    MOODLE_VERSION=4.0

# Install system dependencies ONLY (minimal)
RUN apt-get update && apt-get install -y --no-install-recommends \
    libfreetype6-dev \
    libjpeg-dev \
    libpng-dev \
    libzip-dev \
    libxml2-dev \
    libicu-dev \
    libonig-dev \
    mariadb-client \
    git \
    curl \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions that Moodle actually needs
# Only extensions that exist in PHP 8.2 official image
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    gd \
    mysqli \
    pdo \
    pdo_mysql \
    intl \
    xml \
    zip \
    mbstring \
    soap


# Configure PHP for Moodle
RUN echo "upload_max_filesize = 100M" >> /usr/local/etc/php/conf.d/moodle.ini && \
    echo "post_max_size = 100M" >> /usr/local/etc/php/conf.d/moodle.ini && \
    echo "max_execution_time = 300" >> /usr/local/etc/php/conf.d/moodle.ini && \
    echo "default_charset = 'UTF-8'" >> /usr/local/etc/php/conf.d/moodle.ini && \
    echo "memory_limit = 512M" >> /usr/local/etc/php/conf.d/moodle.ini

# Enable Apache modules
RUN a2enmod rewrite && a2enmod headers

# Create moodledata directory
RUN mkdir -p /var/moodledata && \
    chown -R www-data:www-data /var/moodledata && \
    chmod 755 /var/moodledata

# Copy application files to Apache root
COPY --chown=www-data:www-data . /var/www/html/

# Remove default Apache site
RUN rm /etc/apache2/sites-enabled/000-default.conf

# Create Moodle Apache configuration
RUN cat > /etc/apache2/sites-available/moodle.conf << 'EOF'
<VirtualHost *:80>
    ServerName localhost
    DocumentRoot /var/www/html/public
    
    <Directory /var/www/html/public>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    <Directory /var/www/html>
        Options -Indexes
        AllowOverride All
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/moodle-error.log
    CustomLog ${APACHE_LOG_DIR}/moodle-access.log combined
</VirtualHost>
EOF

# Enable the Moodle site
RUN a2ensite moodle.conf

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html

# Create startup script for database initialization
RUN cat > /usr/local/bin/docker-entrypoint.sh << 'SCRIPT'
#!/bin/bash
set -e

# Set defaults from environment or use docker-compose values
DB_HOST="${DB_HOST:-mysql}"
DB_USER="${DB_USER:-moodle}"
DB_PASSWORD="${DB_PASSWORD:-moodlepass}"
DB_NAME="${DB_NAME:-moodle}"
DB_PORT="${DB_PORT:-3306}"

echo "Database Configuration:"
echo "  Host: $DB_HOST"
echo "  User: $DB_USER"
echo "  Database: $DB_NAME"
echo "  Port: $DB_PORT"

# Wait for MySQL to be ready
echo "Waiting for MySQL to be ready..."
max_attempts=60
attempt=0
while [ $attempt -lt $max_attempts ]; do
    if mariadb-admin ping -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASSWORD" -P"$DB_PORT" --silent 2>/dev/null; then
        echo "✓ MySQL is ready!"
        break
    fi
    attempt=$((attempt + 1))
    if [ $((attempt % 10)) -eq 0 ]; then
        echo "  ... still waiting ($attempt/$max_attempts)"
    fi
    sleep 1
done

if [ $attempt -eq $max_attempts ]; then
    echo "⚠ MySQL connection timeout after $max_attempts attempts!"
    echo "Continuing anyway - database may initialize later..."
fi

# Check if Moodle tables exist
echo "Checking if Moodle database is initialized..."
if ! mariadb -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASSWORD" -P"$DB_PORT" "$DB_NAME" -e "SHOW TABLES LIKE 'mdl_%';" 2>/dev/null | grep -q mdl_; then
    echo "Database is empty. Initializing Moodle..."
    cd /var/www/html
    php admin/cli/install_database.php \
        --adminuser=admin \
        --adminpass=Admin@123456 \
        --adminemail=admin@example.com || echo "⚠ Database initialization attempted (may fail if DB not ready)"
else
    echo "✓ Moodle database already initialized"
fi

echo "Starting Apache..."
exec apache2-foreground
SCRIPT

RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=40s --retries=3 \
    CMD curl -f http://localhost/ || exit 1

# Expose HTTP port
EXPOSE 80

# Start with entrypoint script
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
