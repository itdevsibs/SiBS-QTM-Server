FROM php:8.2-apache

WORKDIR /app

# system deps + PHP extensions and enable rewrite
RUN apt-get update \
  && apt-get install -y libzip-dev unzip zip git libonig-dev zlib1g-dev \
  && docker-php-ext-install pdo_mysql mbstring zip \
  && a2enmod rewrite \
  && rm -rf /var/lib/apt/lists/*

# install composer binary
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# install PHP deps (use composer.json/composer.lock)
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress

# copy application code into /app
COPY . /app

# update apache vhost to serve /app and fix permissions
RUN sed -ri "s!/var/www/html!/app!g" /etc/apache2/sites-available/*.conf \
 && sed -ri "s!/var/www/html!/app!g" /etc/apache2/apache2.conf \
 && chown -R www-data:www-data /app \
 && find /app -type d -exec chmod 755 {} \; \
 && find /app -type f -exec chmod 644 {} \;

 # ensure vhost allows access to /app and enable headers
RUN a2enmod headers rewrite \
 && cat > /etc/apache2/sites-available/000-default.conf <<'EOF'
<VirtualHost *:80>
    DocumentRoot /app
    <Directory /app>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
EOF

EXPOSE 80
CMD ["apache2-foreground"]