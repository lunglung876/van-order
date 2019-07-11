// Install composer dependencies 
docker run --rm -v $(pwd)/code:/app composer install

docker-composer build

docker-composer up -d