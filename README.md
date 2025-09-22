## Notification Service (Email + SMS with automatic failover)

### 1) Requirements
- Docker Desktop (Windows/Mac/Linux)
- Local ports 80/443 available

### 2) Quick start
```bash
git clone <this_repo_url>
cd be-evaluation-task-main
docker compose up --build
```
This starts `php`, `caddy` (reverse proxy), `database` (MariaDB), and `worker` (Symfony Messenger).
Endpoint: `http://localhost/notify`

### 3) Configuration (pre-filled in docker-compose)
- Channels and provider priority (failover):
  - `CHANNEL_EMAIL_ENABLED=1`, `CHANNEL_SMS_ENABLED=1`
  - `EMAIL_PROVIDERS="mailtrap,gmail"` (Gmail as fallback)
  - `SMS_PROVIDERS="twilio,textbelt"` (Textbelt as fallback)
- Rate limit: `NOTIFICATIONS_LIMIT_PER_HOUR=300`
- Email (Gmail SMTP example): `MAILER_DSN`, `GMAIL_FROM`
- SMS:
  - Twilio: `TWILIO_ACCOUNT_SID`, `TWILIO_AUTH_TOKEN`, `TWILIO_FROM` (Account SID must start with `AC...`)
  - Textbelt: `TEXTBELT_API_KEY` (free key usually US/CA only)

Note: The repo intentionally ships with two misconfigured/limited providers to showcase failover out of the box:
- Email: first provider is Mailtrap (not configured) so it fails, then Gmail succeeds.
- SMS: if Twilio credentials are invalid or missing, it fails and falls back to Textbelt (which may be limited for non‑US numbers).
Adjust envs to make both providers live in real deployments.

### 4) Test (Postman/curl)
- Headers: `Content-Type: application/json`
- URL: `http://localhost/notify`

Email example:
```json
{
  "userId": "user-1",
  "channels": ["email"],
  "recipient": "user@example.com",
  "subject": "Hello",
  "content": "World"
}
```

SMS example:
```json
{
  "userId": "user-1",
  "channels": ["sms"],
  "recipient": "+15551234567",
  "subject": "ignored",
  "content": "Test SMS"
}
```

Expected response: `{ "status": "queued" }` – processing is async by the `worker` (Messenger + Doctrine transport).

### 5) Failover behavior
- Provider order is set by `EMAIL_PROVIDERS` and `SMS_PROVIDERS`.
- On provider error, the next provider is used automatically.

### 6) Logs
- Container logs:
  - `docker compose logs -n 200 php`
  - `docker compose logs -n 200 worker`
  - `docker compose logs -n 200 caddy`

### 7) Providers notes
- Gmail: use an App Password or a correct SMTP/SMTPS DSN. TLS CA certificates are included in the image.
- Twilio: requires a valid `TWILIO_ACCOUNT_SID` (format `AC...`). Optionally, a Messaging Service SID can be used.
- Textbelt: free key is typically US/CA only.

### 8) Tests
```bash
docker compose exec php ./vendor/bin/phpunit
```

# Symfony Docker

A [Docker](https://www.docker.com/)-based installer and runtime for the [Symfony](https://symfony.com) web framework, with full [HTTP/2](https://symfony.com/doc/current/weblink.html), HTTP/3 and HTTPS support.

![CI](https://github.com/dunglas/symfony-docker/workflows/CI/badge.svg)

## Getting Started

1. If not already done, [install Docker Compose](https://docs.docker.com/compose/install/) (v2.10+)
2. Run `docker compose build --pull --no-cache` to build fresh images
3. Run `docker compose up` (the logs will be displayed in the current shell)
4. Open `https://localhost` in your favorite web browser and [accept the auto-generated TLS certificate](https://stackoverflow.com/a/15076602/1352334)
5. Run `docker compose down --remove-orphans` to stop the Docker containers.

## Features

* Production, development and CI ready
* [Installation of extra Docker Compose services](docs/extra-services.md) with Symfony Flex
* Automatic HTTPS (in dev and in prod!)
* HTTP/2, HTTP/3 and [Preload](https://symfony.com/doc/current/web_link.html) support
* Built-in [Mercure](https://symfony.com/doc/current/mercure.html) hub
* [Vulcain](https://vulcain.rocks) support
* Native [XDebug](docs/xdebug.md) integration
* Just 2 services (PHP FPM and Caddy server)
* Super-readable configuration

## Docs

1. [Build options](docs/build.md)
2. [Using Symfony Docker with an existing project](docs/existing-project.md)
3. [Support for extra services](docs/extra-services.md)
4. [Deploying in production](docs/production.md)
5. [Debugging with Xdebug](docs/xdebug.md)
6. [TLS Certificates](docs/tls.md)
7. [Using a Makefile](docs/makefile.md)
8. [Troubleshooting](docs/troubleshooting.md)

