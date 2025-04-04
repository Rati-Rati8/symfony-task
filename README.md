# Symfony Docker



## Initial setup

1. [install Docker Compose](https://docs.docker.com/compose/install/) (v2.10+)
2. Run `docker compose up --build` to build fresh images, this command also will run all migrations 
and seed some dummy data for a testing purposes
3. Open to check it works`https://localhost`
4. to connect db locally ` user:app psw:!ChangeMe! db:app`
5. to access rabbitMQ locally http://localhost:15672/#/ . usr:guest psw:guest
6. to run background message consumer execute `docker compose exec php bin/console messenger:consume async -vv`

7. **Important!**: The project utilizes the FastForex API for currency conversions. Please ensure that your API key is correctly set in the .env.dev file. You can obtain an API key by registering at https://console.fastforex.io/.

## Testing
1. Run `docker compose exec php bin/phpunit` to run PHPunit tests
2. Run `docker compose exec php bin/phpunit --coverage-html public/coverage` to check coverages, this will be generated on public folder
3. Project includes postman collection which might be used for testing `postman_collection.json`





