-- Проверка и создание базы данных skill-mentor-service
SELECT 'CREATE DATABASE "skill-mentor-service" WITH TEMPLATE template0'
    WHERE NOT EXISTS (
    SELECT FROM pg_database WHERE datname = 'skill-mentor-service'
)\gexec

-- Проверка и создание базы данных skill-mentor-service_test
SELECT 'CREATE DATABASE "skill-mentor-service_test" WITH TEMPLATE template0'
    WHERE NOT EXISTS (
    SELECT FROM pg_database WHERE datname = 'skill-mentor-service_test'
)\gexec

-- Создание пользователя, если он не существует
DO $$
BEGIN
    IF NOT EXISTS (
        SELECT FROM pg_roles WHERE rolname = 'otus_user'
    ) THEN
        EXECUTE 'CREATE USER "otus_user" WITH ENCRYPTED PASSWORD ''otus_password''';
END IF;
END $$;

-- Назначение прав
GRANT ALL PRIVILEGES ON DATABASE "skill-mentor-service" TO "otus_user";
GRANT ALL PRIVILEGES ON DATABASE "skill-mentor-service_test" TO "otus_user";

-- Подключение к базам данных и назначение привилегий
\c "skill-mentor-service";
GRANT ALL PRIVILEGES ON SCHEMA public TO "otus_user";
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL PRIVILEGES ON TABLES TO "otus_user";

\c "skill-mentor-service_test";
GRANT ALL PRIVILEGES ON SCHEMA public TO "otus_user";
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL PRIVILEGES ON TABLES TO "otus_user";