# Dockerfile for CodeRunner Academy
# Builds a production-ready Moodle container with CodeRunner

FROM php:8.2-apache

ENV DEBIAN_FRONTEND=noninteractive \
    MOODLE_VERSION=4.0

# Install required PHP extensions and system packages
RUN apt-get update && apt-get install -y --no-install-recommends \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libxml2-dev \
    libcurl4-openssl-dev \
    libpq-dev \
    libzip-dev \
    mariadb-client \
    curl \
    git \
    nano \
    wget \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    gd \
    pdo_mysql \
    mysqli \
    intl \
    xml \
    xmlrpc \
    zip \
    curl


# Enable Apache modules
RUN a2enmod rewrite \
    && a2enmod headers

# Create Moodle directories
RUN install -d -m 0755 /var/moodledata \
    && chown -R www-data:www-data /var/www/html \
    && chown -R www-data:www-data /var/moodledata

# Copy application files
COPY --chown=www-data:www-data . /var/www/html/

# Configure PHP
RUN echo "upload_max_filesize = 100M" >> /usr/local/etc/php/conf.d/moodle.ini && \
    echo "post_max_size = 100M" >> /usr/local/etc/php/conf.d/moodle.ini && \
    echo "max_execution_time = 300" >> /usr/local/etc/php/conf.d/moodle.ini && \
    echo "default_charset = 'UTF-8'" >> /usr/local/etc/php/conf.d/moodle.ini

# Configure Apache
RUN a2dissite 000-default && \
    cat > /etc/apache2/sites-available/moodle.conf << 'EOF'
<VirtualHost *:80>
    DocumentRoot /var/www/html/public
    
    <Directory /var/www/html/public>
        Options FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    <Directory /var/www/html>
        Options -Indexes
        AllowOverride None
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/moodle-error.log
    CustomLog ${APACHE_LOG_DIR}/moodle-access.log combined
</VirtualHost>
EOF

RUN a2ensite moodle.conf

# Health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=40s --retries=3 \
    CMD curl -f http://localhost/ || exit 1

# Expose port
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]
