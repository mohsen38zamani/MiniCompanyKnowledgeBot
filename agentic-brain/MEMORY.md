# Memory Log

## Decisions
- Use Laravel 10 because it is compatible with PHP 8.1 and stable for MVP.
- Start with deterministic retrieval before adding LLM-based ranking.
- Keep a versioned API path (`/api/v1/knowledge/ask`) and retain legacy path for compatibility.
- Standardize API errors with machine-friendly `error.code`.
- Include `meta.trace_id` in success responses for log correlation.
- Persist all Q&A interactions in a dedicated `knowledgebot` log channel for auditability.
- Keep development flow on `dev` and promote via manual PRs.

## Problems Encountered
- Initial workspace was not a git repository (`.git` missing), so history and branch workflow had to be initialized from scratch.
- Local branch state was accidentally pushed to `main` and `stage` during early setup, which conflicted with intended `dev-only` development flow.
- Answer quality initially returned overly long concatenated responses instead of concise grounded snippets.
- Several false-positive answers occurred for unsupported questions (notably `mobile app` and `database`) due to broad lexical overlap.
- Section-style document questions (such as support channels/hours and MVP scope) were initially underperforming due to strict token matching.
- Eval matcher produced false negatives for valid answers because of rigid keyword matching rules.
- UI error rendering briefly mismatched the new API error contract and did not correctly read structured error payloads.

## Course Corrections / Change of Direction
- Rebased local history on remote and enforced branch policy: active development on `dev`, while `main` and `stage` were reset to base.
- Shifted from multi-chunk concatenation to precision-first single-snippet answers with a single primary source.
- Added stricter fallback guardrails and intent-aware scoring to reduce false positives.
- Added section-aware extraction for heading+bullet patterns (support channels, support hours, MVP scope).
- Added question-type guard for database queries to force fallback unless database-specific evidence exists in docs.
- Relaxed and normalized eval keyword matching to reduce false negatives while preserving meaningful checks.
- Updated frontend to handle structured validation errors consistently with backend API contract.
- Added dedicated Q&A audit logging (traceable by `trace_id`) and documented daily rotated log path.

## Learnings
- A strict grounded-answer policy reduces noisy responses in early versions.
- A clear document schema in `docs/` improves answer consistency.
- Frontend should parse structured error shape after API contract changes.
- Deterministic ingestion should sort files and ignore unsupported/empty docs for stable behavior.
- Concise answer quality improves when ranking against document-level candidates instead of concatenating multiple chunks.
- A lightweight eval matcher needs token normalization to avoid false negatives on valid answers.
- Section-aware matching is required for heading+bullet style docs (support channels, MVP scope, support hours).
- Generic token overlap alone can create false positives; intent guards and topic-specific fallbacks are necessary.

## Change Notes
- Initial project brief was rewritten to enforce PM + Technical Lead role for AI.
- Added `knowledge:eval` command to automate scenario checks from `EVALS.md`.
- Improved chunking to avoid oversize chunks when lines are too long.
- Refined answer strategy to return one precise grounded snippet with a single source.
- Added complete Q&A logging (question, answer, sources, snippets, trace, endpoint, client IP).
- Added regression tests for reported edge cases (`how long`, `mobile app`, `database`, `support channels`, `support phone`, `MVP scope`).
- Verified HTTP-level batch checks against `sample-questions.txt` with 25/25 pass.
- Consolidated documentation across `README` and `agentic-brain` to reflect real runtime behavior and branch policy.
