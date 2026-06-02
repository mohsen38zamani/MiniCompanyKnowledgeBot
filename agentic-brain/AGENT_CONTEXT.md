# Agent Context

## What This Project Is
A backend-first Laravel knowledge bot for a fictional CRM company. It answers only from local documentation and avoids unsupported claims by using strict fallback logic.

## Source of Truth
- Docs corpus: `docs/`
- Core logic: `app/Services/Knowledge/`
- Web/API entry: `app/Http/Controllers/KnowledgeBotController.php`
- Eval command: `php artisan knowledge:eval`
- Q&A logs: `storage/logs/knowledge-bot-YYYY-MM-DD.log`

## Current Technical Direction
- Deterministic ingestion (`txt`/`md`, sorted, non-empty docs only)
- Safe chunking and intent-aware scoring
- Single concise snippet output with one primary source
- Guardrails for false positives (unsupported topics -> fallback)
- Structured API payload:
  - success: `success`, `data`, `meta`
  - errors: `success=false`, `error.code`, `error.message`, `error.details`

## Branch Strategy
- Active development branch: `dev`
- `main` and `stage` intentionally kept at base state for manual PR flow
- Do not push feature changes directly to `main`/`stage`

## How To Continue
1. Keep controllers thin; push logic into services
2. Add behavior only with matching regression tests
3. Preserve backward compatibility for `/api/knowledge/ask`
4. Keep `/api/v1/*` as preferred contract
5. Keep logs privacy-aware and operationally useful
6. Update `PROJECT_BRIEF.md`, `MEMORY.md`, `TASKS.md`, `EVALS.md` whenever behavior changes
