<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mini Company Knowledge Bot</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 2rem; max-width: 920px; }
        textarea { width: 100%; min-height: 110px; }
        button { margin-top: 0.8rem; padding: 0.6rem 1rem; }
        .result { margin-top: 1.2rem; padding: 1rem; border: 1px solid #ccc; border-radius: 8px; }
        .muted { color: #666; font-size: 0.9rem; }
    </style>
</head>
<body>
    <h1>Mini Company Knowledge Bot</h1>
    <p class="muted">Answers are generated only from files inside <code>docs/</code>.</p>

    <form id="ask-form">
        @csrf
        <label for="question">Your question</label>
        <textarea id="question" name="question" required minlength="3" maxlength="500"></textarea>
        <button type="submit">Ask</button>
    </form>

    <div id="result" class="result" style="display:none;">
        <h3>Answer</h3>
        <p id="answer-text"></p>
        <p class="muted"><strong>Sources:</strong> <span id="sources-text">-</span></p>
    </div>

    <script>
        const form = document.getElementById('ask-form');
        const resultBox = document.getElementById('result');
        const answerText = document.getElementById('answer-text');
        const sourcesText = document.getElementById('sources-text');

        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            const formData = new FormData(form);
            const csrfToken = formData.get('_token');
            const question = formData.get('question');

            const response = await fetch('{{ route('knowledge.ask') }}', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ question })
            });

            const payload = await response.json();
            resultBox.style.display = 'block';

            if (!response.ok) {
                answerText.textContent = payload?.error?.message || payload.message || 'Validation failed.';
                sourcesText.textContent = '-';
                return;
            }

            const data = payload.data || {};

            answerText.textContent = data.answer || 'No answer returned.';
            sourcesText.textContent = (data.sources && data.sources.length > 0)
                ? data.sources.join(', ')
                : 'No supporting source found';
        });
    </script>
</body>
</html>
