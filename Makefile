.PHONY: init lock up down seed seed-force test stan css

lock:
	docker run --rm -v "$(PWD):/app" -w /app composer:2 update
	@echo "composer.lock created. Commit: git add composer.lock"

init:
	composer install

up:
	docker compose up --build -d

down:
	docker compose down

seed:
	docker compose exec app php bin/seed.php

seed-force:
	docker compose exec app php bin/seed.php --force

test:
	./vendor/bin/phpunit

stan:
	./vendor/bin/phpstan analyse

css:
	docker run --rm -v "$(PWD):/app" -w /app sass/sass scss/main.scss public/css/style.css --style=compressed