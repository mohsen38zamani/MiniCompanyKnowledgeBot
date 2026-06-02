# Mini Company Knowledge Bot

Backend-first Laravel application that answers user questions using only local company documents under `docs/`.

## Stack
- Laravel 10 (PHP 8.1 compatible)
- Deterministic retrieval pipeline (no external vector database)
- Simple web UI + JSON API
- Full Q&A audit logging

## Requirements
- PHP 8.1+
- Composer 2+

## Project Structure
- `docs/`: source knowledge documents
- `app/Services/Knowledge/`: retrieval and answering logic
- `agentic-brain/`: project memory and execution artifacts
- `sample-questions.txt`: grounded/fallback smoke test question set

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

Success response shape:
```json
{
  "success": true,
  "data": {
    "question": "What is ParsCRM?",
    "answer": "ParsCRM is a lightweight customer relationship management platform...",
    "sources": ["faq"],
    "snippets": ["ParsCRM is a lightweight customer relationship management platform..."]
  },
  "meta": {
    "grounded": true,
    "fallback": false,
    "trace_id": "uuid"
  }
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

Quick smoke-check for full sample set:
```bash
php -r 'require "vendor/autoload.php"; $app=require "bootstrap/app.php"; $kernel=$app->make(Illuminate\Contracts\Console\Kernel::class); $kernel->bootstrap(); $svc=$app->make(App\Services\Knowledge\AnswerService::class); $lines=file("sample-questions.txt", FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES); $mode="grounded"; $pass=0; $total=0; foreach($lines as $line){ $trim=trim($line); if(str_starts_with($trim,"Fallback-check questions")){ $mode="fallback"; continue;} $parts=explode(") ",$trim,2); if(count($parts)!==2 || !ctype_digit($parts[0])){ continue;} $q=$parts[1]; $r=$svc->answer($q); $ok = $mode==="grounded" ? ($r["sources"]!==[]) : ($r["sources"]===[]); $total++; if($ok){$pass++;} } echo "RESULT: $pass/$total\n";'
```

## Q&A Logs
All question/answer interactions are logged to:
- `storage/logs/knowledge-bot-YYYY-MM-DD.log` (daily rotated)

Each entry includes question, answer, sources, snippets, grounded/fallback flags, `trace_id`, endpoint, and client IP.
