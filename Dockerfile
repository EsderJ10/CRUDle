FROM php:8.1-apache

# Set working directory
WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install -j$(nproc) gd pdo pdo_mysql \
    && docker-php-ext-enable gd pdo pdo_mysql

# Enable Apache mod_rewrite for clean URLs
RUN a2enmod rewrite

# Copy Apache configuration
COPY docker/apache-config.conf /etc/apache2/sites-available/000-default.conf

# Copy application files
COPY . .

# Copy and set up entrypoint
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Create necessary directories and set permissions
RUN mkdir -p data logs uploads/avatars \
    && chmod -R 755 data logs uploads \
    && chown -R www-data:www-data /var/www/html

# Expose port 8080
EXPOSE 8080

# Start Apache via entrypoint
ENTRYPOINT ["entrypoint.sh"]
CMD ["apache2-foreground"]
