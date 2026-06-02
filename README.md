# Mini Company Knowledge Bot

Backend-first Laravel application that answers user questions using only local company documents under `docs/`.

## Stack
- Laravel 10 (PHP 8.1 compatible)
- Deterministic retrieval pipeline (no external vector database)
- Simple web UI + JSON API

## Project Structure
- `docs/`: source knowledge documents
- `app/Services/Knowledge/`: retrieval and answering logic
- `agentic-brain/`: project memory and execution artifacts

## Run
1. `cp .env.example .env`
2. `composer install`
3. `php artisan key:generate`
4. `php artisan serve`

Then open `http://127.0.0.1:8000`.

## API
- `POST /ask`
- `POST /api/knowledge/ask`

Payload:
```json
{
  "question": "What is ParsCRM?"
}
```

## Tests
Run `php artisan test`.
