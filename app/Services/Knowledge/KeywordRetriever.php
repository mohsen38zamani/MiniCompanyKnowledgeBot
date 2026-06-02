<?php

namespace App\Services\Knowledge;

class KeywordRetriever
{
    /**
     * @param array<int, array{source: string, text: string}> $chunks
     * @return array<int, array{source: string, text: string, score: int}>
     */
    public function topRelevantChunks(string $question, array $chunks, int $topK = 3): array
    {
        $questionTokens = $this->tokenize($question);
        if ($questionTokens === []) {
            return [];
        }

        $scored = [];

        foreach ($chunks as $chunk) {
            $chunkTokens = $this->tokenize($chunk['text']);
            $score = count(array_intersect($questionTokens, $chunkTokens));

            if ($score > 0) {
                $scored[] = [
                    'source' => $chunk['source'],
                    'text' => $chunk['text'],
                    'score' => $score,
                ];
            }
        }

        usort(
            $scored,
            fn (array $left, array $right) => $right['score'] <=> $left['score']
        );

        return array_slice($scored, 0, $topK);
    }

    /**
     * @return array<int, string>
     */
    private function tokenize(string $text): array
    {
        $text = mb_strtolower($text);
        $parts = preg_split('/[^a-z0-9]+/i', $text) ?: [];
        $parts = array_filter($parts, fn (string $token) => strlen($token) >= 3);

        return array_values(array_unique($parts));
    }
}
