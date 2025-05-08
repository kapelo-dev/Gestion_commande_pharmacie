FROM php:8.2-apache

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
    npm \
    && rm -rf /var/lib/apt/lists/*

# Installation des extensions PHP
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Installation de Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configuration de Composer
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV COMPOSER_HOME=/composer
ENV PATH="${COMPOSER_HOME}/vendor/bin:${PATH}"

# Configuration d'Apache
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
RUN a2enmod rewrite

# Création du répertoire de travail
WORKDIR /var/www/html

# Copie des fichiers de dépendances
COPY composer.* ./
COPY package*.json ./

# Installation des dépendances PHP avec plus de verbosité
RUN set -x && \
    composer install \
    --no-dev \
    --no-interaction \
    --no-scripts \
    --no-autoloader \
    --prefer-dist \
    --verbose

# Installation des dépendances Node.js
RUN npm install

# Copie du reste des fichiers du projet
COPY . .

# Génération de l'autoloader optimisé
RUN composer dump-autoload --optimize --no-dev

# Build des assets
RUN npm run build

# Configuration des permissions
RUN chown -R www-data:www-data \
    /var/www/html/storage \
    /var/www/html/bootstrap/cache \
    /var/www/html/public

# Exposition du port
EXPOSE 80

# Commande de démarrage
CMD ["apache2-foreground"] 