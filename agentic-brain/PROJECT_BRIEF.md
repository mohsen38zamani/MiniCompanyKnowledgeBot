# Mini Company Knowledge Bot

## Project Goal
Build a Laravel-based knowledge bot that answers user questions using only local company documents in `docs/`, with deterministic and explainable behavior.

## Current MVP Scope
- Laravel 10 backend (PHP 8.1 compatible)
- Web ask endpoint and versioned API endpoint
- Deterministic retrieval/answering without external vector database or LLM dependency
- Strict fallback behavior when information does not exist in docs
- Structured JSON success/error contract
- Full Q&A audit logging for traceability
- Eval command and feature tests for repeatable quality checks

## In-Scope Features (Implemented)
- Load and normalize local docs (`txt`/`md`)
- Chunk text safely for retrieval
- Rank candidates with intent-aware scoring and guardrails
- Return concise, single-snippet grounded answers
- Return machine-readable fallback responses
- Log question, answer, sources, trace_id, endpoint, and client IP

## Out of Scope (Current MVP)
- External embedding/vector services
- Multi-tenant auth/authorization
- Admin dashboard for analytics
- Real-time streaming answers

## Constraints
- Keep architecture simple and maintainable
- Preserve separation of concerns across layers
- Prefer deterministic behavior over opaque heuristics
- Keep commits meaningful and reviewable
