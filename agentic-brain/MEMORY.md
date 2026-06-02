# Memory Log

## Decisions
- Use Laravel 10 because it is compatible with PHP 8.1 and stable for MVP.
- Start with deterministic retrieval before adding LLM-based ranking.
- Keep a versioned API path (`/api/v1/knowledge/ask`) and retain legacy path for compatibility.
- Standardize API errors with machine-friendly `error.code`.
- Include `meta.trace_id` in success responses for log correlation.

## Learnings
- A strict grounded-answer policy reduces noisy responses in early versions.
- A clear document schema in `docs/` improves answer consistency.
- Frontend should parse structured error shape after API contract changes.
- Deterministic ingestion should sort files and ignore unsupported/empty docs for stable behavior.
- Concise answer quality improves when ranking against document-level candidates instead of concatenating multiple chunks.
- A lightweight eval matcher needs token normalization to avoid false negatives on valid answers.

## Change Notes
- Initial project brief was rewritten to enforce PM + Technical Lead role for AI.
- Added `knowledge:eval` command to automate scenario checks from `EVALS.md`.
- Improved chunking to avoid oversize chunks when lines are too long.
- Refined answer strategy to return one precise grounded snippet with a single source.
