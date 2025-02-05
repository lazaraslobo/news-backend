#!/bin/bash

# Ensure necessary directories exist
mkdir -p ./storage/logs ./storage/framework/sessions ./bootstrap/cache

# Adjust ownership and permissions
sudo chown -R $USER:www-data ./storage ./bootstrap/cache
sudo chmod -R 775 ./storage ./bootstrap/cache

# Specific permissions for logs
sudo chmod -R g+w ./storage/logs

sudo chmod o+w ./storage/ -R

sudo chown www-data:www-data -R ./storage

# Clear Laravel caches
#php artisan optimize:clear
