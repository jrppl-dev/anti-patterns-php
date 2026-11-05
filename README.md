# Todo

A small Symfony 7.4 todo app: users, lists and tasks, with web pages and a JSON API.

## Run
- `php bin/console doctrine:migrations:migrate -n` creates the SQLite database in `var/`.
- `php bin/console app:seed` loads Alice and Bob with a list each.
- `php -S 127.0.0.1:8000 -t public`, then open http://127.0.0.1:8000 and pick a user.

## Docker
`docker compose -f docker/compose.yaml up` installs dependencies, migrates and serves on http://127.0.0.1:8000. Run the tests with `docker compose -f docker/compose.yaml run --rm app php vendor/bin/phpunit`.

## API
Send the acting user's id in the `X-User-Id` header.
- `GET /api/lists/{id}/tasks`
- `POST /api/lists/{id}/tasks` with `{"title", "description", "priority": 1-3, "dueDate": "Y-m-d"}`
- `PATCH /api/tasks/{id}` with any of those fields and `status` (todo, doing, done)

## Tests
`php vendor/bin/phpunit`. The bootstrap rebuilds `var/data_test.db` from the migrations.
