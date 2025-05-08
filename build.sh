#!/bin/bash
php artisan config:clear
php artisan route:clear
php artisan cache:clear
php artisan view:clear
npm install
npm run build
cp -r public dist 