# Tasks

## Done
- [x] Initialize repository from scratch
- [x] Add structured execution brief
- [x] Bootstrap Laravel application
- [x] Create initial `docs/` knowledge files
- [x] Create required `agentic-brain/` files
- [x] Implement document loader service
- [x] Implement chunking and retrieval service
- [x] Build answering service with fallback
- [x] Create web form and API endpoints
- [x] Add eval scenarios and tests
- [x] Standardize API responses and structured errors
- [x] Add `api/v1` route and trace logging
- [x] Add eval automation command (`knowledge:eval`)
- [x] Improve deterministic ingestion and chunk safety
- [x] Improve answer precision to return concise single-snippet responses
- [x] Tune eval matcher to reduce false negative checks
- [x] Log all Q&A interactions to a dedicated `knowledgebot` log file
- [x] Resolve edge-case false positives from live logs
- [x] Add section-aware scoring for heading+bullet document patterns
- [x] Add regression coverage for reported user questions
- [x] Validate HTTP endpoint behavior with batch sample questions (`25/25`)

## Next
- [ ] Add rate limiting for ask endpoints
- [ ] Add response-time and error metrics dashboarding
- [ ] Add optional citation offsets per snippet
- [ ] Add caching for docs/chunks to reduce repeated IO
- [ ] Add an Artisan command to run `sample-questions.txt` and print pass/fail report
- [ ] Add privacy guard for log content masking (if sensitive data appears in future docs)
