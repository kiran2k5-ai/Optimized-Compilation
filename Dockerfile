# Dockerfile for CodeRunner Academy
# Builds a production-ready Moodle container with CodeRunner

FROM ubuntu:22.04

ENV DEBIAN_FRONTEND=noninteractive \
    PHP_VERSION=8.2 \
    MOODLE_VERSION=4.0

# Add PHP repository for more packages
RUN apt-get update && apt-get install -y \
    software-properties-common \
    && add-apt-repository ppa:ondrej/php \
    && apt-get update

# Install base packages
RUN apt-get install -y \
    apache2 \
    apache2-utils \
    php${PHP_VERSION} \
    php${PHP_VERSION}-common \
    php${PHP_VERSION}-mysql \
    php${PHP_VERSION}-curl \
    php${PHP_VERSION}-gd \
    php${PHP_VERSION}-intl \
    php${PHP_VERSION}-xml \
    php${PHP_VERSION}-zip \
    php${PHP_VERSION}-mbstring \
    mariadb-client \
    curl \
    git \
    nano \
    wget \
    && rm -rf /var/lib/apt/lists/*

# Enable Apache modules
RUN a2enmod rewrite \
    && a2enmod headers \
    && a2enmod ssl

# Create Moodle directories
RUN mkdir -p /var/www/html/moodle \
    && mkdir -p /var/moodledata \
    && chown -R www-data:www-data /var/www/html \
    && chown -R www-data:www-data /var/moodledata \
    && chmod 755 /var/moodledata

# Copy application files
COPY . /var/www/html/moodle/

# Copy security files
RUN chown -R www-data:www-data /var/www/html/moodle && \
    chmod 755 /var/www/html/moodle

# Configure PHP
RUN echo "upload_max_filesize = 100M" >> /etc/php/${PHP_VERSION}/apache2/php.ini && \
    echo "post_max_size = 100M" >> /etc/php/${PHP_VERSION}/apache2/php.ini && \
    echo "max_execution_time = 300" >> /etc/php/${PHP_VERSION}/apache2/php.ini

# Configure Apache
RUN a2dissite 000-default && \
    cat > /etc/apache2/sites-available/moodle.conf << 'EOF'
<VirtualHost *:80>
    DocumentRoot /var/www/html/moodle/public
    
    <Directory /var/www/html/moodle/public>
        Options FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    <Directory /var/www/html/moodle>
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
EXPOSE 80 443

# Start Apache
CMD ["/usr/sbin/apache2ctl", "-D", "FOREGROUND"]
