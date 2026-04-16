#!/bin/bash
# Build script for Render.com deployment
# This script prepares the Moodle application for deployment

set -e

echo "🔨 Building CodeRunner Academy..."

# Install composer dependencies if composer.json exists
if [ -f "composer.json" ]; then
    echo "📦 Installing PHP dependencies..."
    composer install --no-interaction --no-dev --prefer-dist
fi

# Create necessary directories
echo "📁 Creating directories..."
mkdir -p /var/www/html/moodle/public
mkdir -p /var/moodledata
mkdir -p /var/log/apache2

# Set permissions
echo "🔒 Setting permissions..."
chmod -R 755 .
chmod -R 777 /var/moodledata

echo "✅ Build complete!"
