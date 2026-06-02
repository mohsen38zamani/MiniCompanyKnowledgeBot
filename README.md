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
- `POST /ask` (web endpoint)
- `POST /api/v1/knowledge/ask` (versioned API, recommended)
- `POST /api/knowledge/ask` (legacy compatibility)

Payload:
```json
{
  "question": "What is ParsCRM?"
}
```

Success response includes `meta.trace_id` for log correlation.
Validation errors return a structured shape:
```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_FAILED",
    "message": "The given data was invalid.",
    "details": {
      "question": ["The question field must be at least 3 characters."]
    }
  }
}
```

## Tests
Run `php artisan test`.

## Eval Automation
Run `php artisan knowledge:eval` to execute scenarios from `agentic-brain/EVALS.md`.
