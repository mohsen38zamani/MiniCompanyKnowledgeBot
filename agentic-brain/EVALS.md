# Evaluation Scenarios

## Eval 1
- Question: What is ParsCRM?
- Expected: Mentions it is a CRM platform for small and medium businesses in Iran.

## Eval 2
- Question: What are support hours on Thursday?
- Expected: Thursday support is 09:00 to 13:00 Iran time.

## Eval 3
- Question: Which modules are in the product?
- Expected: Mentions pipeline, contact history, ticketing, and reporting.

## Eval 4
- Question: Do you provide a free trial?
- Expected: Mentions 14-day trial with core features.

## Eval 5
- Question: What is the refund policy?
- Expected: Fallback response that information is not available in docs.

## Additional Regression Checks (HTTP + Service)
- How long is the ParsCRM free trial? -> should include `14-day`.
- Does ParsCRM have a mobile app? -> should return fallback.
- What database does ParsCRM use? -> should return fallback.
- What support channels are available? -> should return support channels block.
- What is the support phone number? -> should include `+98-21-0000-0000`.
- What does the MVP scope include in this project? -> should return grounded answer from product docs.

## Latest Verification Snapshot
- `php artisan knowledge:eval`: 5/5 PASS
- Feature tests: PASS (including regression scenarios)
- HTTP batch check against `sample-questions.txt`: 25/25 PASS
