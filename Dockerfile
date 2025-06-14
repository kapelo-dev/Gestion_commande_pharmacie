# Étape 1 : Image de base avec PHP 8.2
FROM php:8.2-cli

# Installer les dépendances système pour PHP + Node.js
RUN apt-get update && apt-get install -y \
    git unzip curl zip libzip-dev libpng-dev libonig-dev libxml2-dev libpq-dev \
    nodejs npm gnupg ca-certificates lsb-release

# Activer les extensions PHP nécessaires
RUN docker-php-ext-install pdo pdo_pgsql zip mbstring

# Installer Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Installer Node.js 18+ (si version trop ancienne dans les dépôts)
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - && \
    apt-get install -y nodejs

# Définir le dossier de travail
WORKDIR /var/www

# Copier tous les fichiers du projet Laravel
COPY . .

# Installer les dépendances Laravel
RUN composer install --no-dev --optimize-autoloader

# Installer les dépendances frontend et compiler
RUN npm install && npm run build

# Donner les bons droits d'accès
RUN chmod -R 775 storage bootstrap/cache

# Exposer le port requis par Render
EXPOSE 10000

# Démarrer Laravel avec le serveur PHP intégré
CMD php artisan serve --host=0.0.0.0 --port=10000