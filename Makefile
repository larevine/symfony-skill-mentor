b:
	docker compose build

up:
	docker compose up -d

down:
	docker compose down --remove-orphans

sh:
	docker exec -it php sh