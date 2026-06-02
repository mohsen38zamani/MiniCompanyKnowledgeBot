# Agent Context

## What This Project Is
A backend-first Laravel project that provides grounded Q&A on a fictional CRM company using local text files.

## Current Technical Direction
- Read plain text files from `docs/`
- Split text into small chunks
- Rank chunks by lexical overlap with user question
- Generate response from highest-ranked chunks only

## How To Continue
1. Keep services in `app/Services/Knowledge/`
2. Keep controllers thin
3. Add tests for retrieval and fallback behavior
4. Update `MEMORY.md` and `TASKS.md` when decisions change
