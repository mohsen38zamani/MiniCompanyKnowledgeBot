<?php

namespace App\Console\Commands;

use App\Services\Knowledge\AnswerService;
use Illuminate\Console\Command;

class RunKnowledgeEvals extends Command
{
    protected $signature = 'knowledge:eval';

    protected $description = 'Run evaluation scenarios from agentic-brain/EVALS.md';

    public function handle(AnswerService $answerService): int
    {
        $evalPath = base_path('agentic-brain/EVALS.md');
        if (! file_exists($evalPath)) {
            $this->error('EVALS.md file not found.');

            return self::FAILURE;
        }

        $content = file_get_contents($evalPath);
        if ($content === false) {
            $this->error('Could not read EVALS.md.');

            return self::FAILURE;
        }

        $scenarios = $this->parseScenarios($content);
        if ($scenarios === []) {
            $this->error('No valid eval scenarios found in EVALS.md.');

            return self::FAILURE;
        }

        $passed = 0;
        foreach ($scenarios as $index => $scenario) {
            $result = $answerService->answer($scenario['question']);
            $answer = mb_strtolower($result['answer']);
            $expected = mb_strtolower($scenario['expected']);

            $isFallbackExpected = str_contains($expected, 'fallback');
            $ok = $isFallbackExpected
                ? $result['sources'] === []
                : $this->matchesExpectedKeywords($expected, $answer);

            $label = 'Eval '.($index + 1);
            if ($ok) {
                $passed++;
                $this->info($label.': PASS');
            } else {
                $this->warn($label.': FAIL');
                $this->line('  Question: '.$scenario['question']);
                $this->line('  Expected: '.$scenario['expected']);
                $this->line('  Actual: '.$result['answer']);
            }
        }

        $total = count($scenarios);
        $this->newLine();
        $this->line("Result: {$passed}/{$total} evals passed.");

        return $passed === $total ? self::SUCCESS : self::FAILURE;
    }

    /**
     * @return array<int, array{question: string, expected: string}>
     */
    private function parseScenarios(string $content): array
    {
        preg_match_all(
            '/- Question:\s*(.+)\R- Expected:\s*(.+)/i',
            $content,
            $matches,
            PREG_SET_ORDER
        );

        return array_map(
            fn (array $match) => [
                'question' => trim($match[1]),
                'expected' => trim($match[2]),
            ],
            $matches
        );
    }

    private function matchesExpectedKeywords(string $expected, string $answer): bool
    {
        $keywords = preg_split('/[^a-z0-9]+/i', $expected) ?: [];
        $keywords = array_values(array_filter($keywords, fn (string $token) => strlen($token) >= 5));

        if ($keywords === []) {
            return true;
        }

        $hits = 0;
        foreach ($keywords as $keyword) {
            $normalizedKeyword = str_ends_with($keyword, 's') && strlen($keyword) > 4
                ? substr($keyword, 0, -1)
                : $keyword;

            if (str_contains($answer, $keyword) || str_contains($answer, $normalizedKeyword)) {
                $hits++;
            }
        }

        $hitRatio = $hits / max(1, count($keywords));
        return $hits >= min(2, count($keywords)) || $hitRatio >= 0.4;
    }
}
