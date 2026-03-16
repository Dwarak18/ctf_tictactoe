FROM php:8.1-apache

WORKDIR /var/www/html
COPY . /var/www/html/

RUN apt-get update \
	&& apt-get install -y --no-install-recommends libsqlite3-dev pkg-config \
	&& rm -rf /var/lib/apt/lists/* \
	&& docker-php-ext-install pdo_sqlite \
	&& touch /var/www/html/database.db \
	&& chown -R www-data:www-data /var/www/html \
	&& chmod 664 /var/www/html/database.db

EXPOSE 80
CMD ["apache2-foreground"]
