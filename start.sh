#!/bin/bash

# Install composer dependencies
docker run --rm -v $(pwd)/code:/app composer install
docker-compose build
docker-compose up -d
# Create database
docker-compose exec php ./bin/console doctrine:database:create --no-interaction -e prod
# Update database schema
docker-compose exec php ./bin/console doctrine:migrations:migrate --no-interaction -e prod
