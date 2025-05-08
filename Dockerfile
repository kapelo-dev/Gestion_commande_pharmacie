FROM php:8.1-apache

# Installation des dépendances système
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    nodejs \
    npm

# Installation des extensions PHP
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Installation de Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configuration d'Apache
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
RUN a2enmod rewrite

# Copie des fichiers du projet
WORKDIR /var/www/html
COPY composer.json composer.lock ./

# Installation des dépendances Composer
RUN composer install --no-dev --no-scripts --no-autoloader

# Copie du reste des fichiers
COPY . .

# Finalisation de Composer
RUN composer dump-autoload --optimize --no-dev

# Installation et build des assets
RUN npm install && npm run build

# Permissions pour Laravel
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Port d'exposition
EXPOSE 80

# Commande de démarrage
CMD ["apache2-foreground"] 