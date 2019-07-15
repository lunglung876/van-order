#!/bin/bash

# Install composer dependencies
docker run --rm -v $(pwd)/code:/app composer install --no-dev
docker-compose build
docker-compose up -d

# Wait for MySQL to get ready
sleep 15

docker-compose exec php ./bin/console cache:clear -e prod
# Create database
docker-compose exec php ./bin/console doctrine:database:create --no-interaction -e prod
# Update database schema
docker-compose exec php ./bin/console doctrine:migrations:migrate --no-interaction -e prod
