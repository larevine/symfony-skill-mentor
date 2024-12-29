CREATE DATABASE "skill-mentor-service";
CREATE DATABASE "skill-mentor-service-test";
CREATE USER "otus_user" WITH ENCRYPTED PASSWORD 'otus_password';
GRANT ALL PRIVILEGES ON DATABASE "skill-mentor-service" TO "otus_user";
GRANT ALL PRIVILEGES ON DATABASE "skill-mentor-service-test" TO "otus_user";

\c "skill-mentor-service";
GRANT ALL PRIVILEGES ON SCHEMA public TO "otus_user";
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL PRIVILEGES ON TABLES TO "otus_user";

\c "skill-mentor-service-test";
GRANT ALL PRIVILEGES ON SCHEMA public TO "otus_user";
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL PRIVILEGES ON TABLES TO "otus_user";