FROM php:7.4-apache

# Enable required PHP extensions
RUN docker-php-ext-install pdo pdo_sqlite

# Install sqlite3 CLI (useful for debugging)
RUN apt-get update && apt-get install -y --no-install-recommends \
        sqlite3 \
    && rm -rf /var/lib/apt/lists/*

# Enable Apache mod_rewrite (for clean URLs if needed)
RUN a2enmod rewrite

# Copy web application files
COPY www/ /var/www/html/

# Create persistent data directory (database + loot file live here)
RUN mkdir -p /var/www/data && chown www-data:www-data /var/www/data

# Allow .htaccess overrides
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 80
ENTRYPOINT ["/entrypoint.sh"]
