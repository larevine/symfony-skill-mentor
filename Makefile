b:
	docker compose build

up:
	docker compose up -d

d:
	docker compose down --remove-orphans

sh:
	docker exec -it otus-php-1 sh

ps:
	docker compose ps