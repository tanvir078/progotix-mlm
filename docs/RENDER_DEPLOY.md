# Render Deployment

This project is now prepared for Render using a Blueprint file at `render.yaml`.

## What gets created

- `progotix-web`: Laravel web application
- `progotix-worker`: database queue worker
- `progotix-db`: PostgreSQL database

## Before you deploy

1. Push this repository to GitHub, GitLab, or Bitbucket.
2. In Render, create a new Blueprint and point it at the repo.
3. During setup, fill in `APP_URL` with your final Render URL.

## Notes

- `APP_KEY` must be a real Laravel app key, for example the output of `php artisan key:generate --show`.
- The web service runs migrations on startup.
- Sessions, cache, and queues all use the database.
- The worker service runs `php artisan queue:work`.

## Recommended first deploy flow

1. Push repo to remote.
2. Import Blueprint from `render.yaml`.
3. Wait for database creation.
4. Generate an app key locally with `php artisan key:generate --show`.
5. Set `APP_KEY` and `APP_URL` in Render.
6. Redeploy the web service once.

## Local validation

- `php artisan test`
- `php artisan migrate --seed --force`
