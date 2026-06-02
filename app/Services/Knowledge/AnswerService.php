<?php

namespace App\Services\Knowledge;

class AnswerService
{
    /**
     * @var array<int, string>
     */
    private array $entityTokens = ['parscrm', 'crm'];
    /**
     * @var array<int, string>
     */
    private array $intentTokens = [
        'email', 'phone', 'hour', 'thursday', 'friday', 'saturday', 'sunday',
        'monday', 'tuesday', 'wednesday', 'critical', 'high', 'normal',
        'module', 'mvp', 'scope', 'goal', 'channel',
    ];

    /**
     * @var array<int, string>
     */
    private array $stopWords = [
        'what', 'which', 'where', 'when', 'how', 'does', 'is', 'are', 'the', 'and', 'for', 'with', 'from', 'into', 'about', 'policy',
        'use', 'uses', 'using', 'available',
    ];

    public function __construct(
        private readonly DocumentLoader $documentLoader,
        private readonly TextChunker $textChunker,
        private readonly KeywordRetriever $keywordRetriever
    ) {
    }

    /**
     * @return array{
     *   answer: string,
     *   sources: array<int, string>,
     *   snippets: array<int, string>
     * }
     */
    public function answer(string $question): array
    {
        $documents = $this->documentLoader->loadFromDocsDirectory();
        if ($documents === []) {
            return $this->fallbackResponse();
        }

        $questionTokens = $this->tokenize($question);
        if ($questionTokens === []) {
            return $this->fallbackResponse();
        }

        $best = null;
        $bestScore = 0.0;

        foreach ($documents as $document) {
            $candidate = $this->bestCandidateForDocument($question, $questionTokens, $document['content']);
            if ($candidate !== null && $candidate['score'] > $bestScore) {
                $bestScore = $candidate['score'];
                $best = [
                    'snippet' => $candidate['snippet'],
                    'source' => $document['title'],
                ];
            }
        }

        if ($best === null || $bestScore < 0.34) {
            return $this->fallbackResponse();
        }
        if ($this->isDatabaseQuestion($questionTokens) && ! $this->looksLikeDatabaseAnswer($best['snippet'])) {
            return $this->fallbackResponse();
        }

        return [
            'answer' => $best['snippet'],
            'sources' => [$best['source']],
            'snippets' => [$best['snippet']],
        ];
    }

    /**
     * @param array<int, string> $questionTokens
     * @return array{snippet: string, score: float}|null
     */
    private function bestCandidateForDocument(string $question, array $questionTokens, string $content): ?array
    {
        $candidates = [];

        $qa = $this->bestQaCandidate($questionTokens, $content);
        if ($qa !== null) {
            $candidates[] = $qa;
        }

        $moduleBlock = $this->extractModuleBlockIfRelevant($question, $content);
        if ($moduleBlock !== null) {
            $candidates[] = ['snippet' => $moduleBlock, 'score' => 0.9];
        }

        $sectionBlock = $this->extractSectionBlockIfRelevant($question, $content);
        if ($sectionBlock !== null) {
            $sectionScore = $this->overlapScore($questionTokens, $this->tokenize($sectionBlock));
            if ($sectionScore > 0) {
                $candidates[] = ['snippet' => $sectionBlock, 'score' => $sectionScore + 0.4];
            }
        }

        $lines = preg_split('/\R+/', $content) ?: [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, 'Q:')) {
                continue;
            }
            if (str_ends_with($line, ':')) {
                continue;
            }

            $lineTokens = $this->tokenize($line);
            $score = $this->overlapScore($questionTokens, $lineTokens);
            if ($score > 0) {
                $candidates[] = ['snippet' => $line, 'score' => $score];
            }
        }

        if ($candidates === []) {
            return null;
        }

        usort($candidates, fn (array $a, array $b) => $b['score'] <=> $a['score']);
        return $candidates[0];
    }

    /**
     * @param array<int, string> $questionTokens
     * @return array{snippet: string, score: float}|null
     */
    private function bestQaCandidate(array $questionTokens, string $content): ?array
    {
        preg_match_all('/Q:\s*(.+?)\R\s*A:\s*(.+?)(?=\R\s*Q:|$)/is', $content, $pairs, PREG_SET_ORDER);
        if ($pairs === []) {
            return null;
        }

        $best = null;
        $bestScore = 0.0;

        foreach ($pairs as $pair) {
            $questionLine = trim($pair[1]);
            $answerLine = trim($pair[2]);
            $score = $this->overlapScore($questionTokens, $this->tokenize($questionLine));

            if ($score > $bestScore) {
                $bestScore = $score;
                $best = [
                    'snippet' => $answerLine,
                    'score' => $score,
                ];
            }
        }

        return $best;
    }

    private function extractModuleBlockIfRelevant(string $question, string $content): ?string
    {
        if (! str_contains(mb_strtolower($question), 'module')) {
            return null;
        }

        $lines = preg_split('/\R+/', $content) ?: [];
        foreach ($lines as $index => $line) {
            if (stripos(trim($line), 'core modules:') !== 0) {
                continue;
            }

            $block = ['Core modules:'];
            for ($i = $index + 1; $i < count($lines); $i++) {
                $next = trim($lines[$i]);
                if (! str_starts_with($next, '-')) {
                    break;
                }
                $block[] = $next;
            }

            if (count($block) > 1) {
                return implode(' ', $block);
            }
        }

        return null;
    }

    private function extractSectionBlockIfRelevant(string $question, string $content): ?string
    {
        $questionTokens = $this->tokenize($question);
        if ($questionTokens === []) {
            return null;
        }

        $lines = preg_split('/\R+/', $content) ?: [];
        foreach ($lines as $index => $line) {
            $line = trim($line);
            if (! str_ends_with($line, ':')) {
                continue;
            }

            $headerTokens = $this->tokenize(rtrim($line, ':'));
            if ($headerTokens === []) {
                continue;
            }

            $headerHits = count(array_intersect($questionTokens, $headerTokens));
            if ($headerHits === 0) {
                continue;
            }

            $block = [$line];
            for ($i = $index + 1; $i < count($lines); $i++) {
                $next = trim($lines[$i]);
                if ($next === '') {
                    continue;
                }
                if (str_ends_with($next, ':')) {
                    break;
                }
                if (! str_starts_with($next, '-')) {
                    break;
                }
                $block[] = $next;
            }

            if (count($block) > 1) {
                return implode(' ', $block);
            }
        }

        return null;
    }

    /**
     * @return array<int, string>
     */
    private function tokenize(string $text): array
    {
        $text = mb_strtolower($text);
        $parts = preg_split('/[^a-z0-9]+/i', $text) ?: [];
        $parts = array_filter(
            $parts,
            fn (string $token) => strlen($token) >= 3 && ! in_array($token, $this->stopWords, true)
        );
        $parts = array_map(function (string $token): string {
            return str_ends_with($token, 's') && strlen($token) > 4
                ? substr($token, 0, -1)
                : $token;
        }, $parts);

        return array_values(array_unique($parts));
    }

    /**
     * @param array<int, string> $questionTokens
     * @param array<int, string> $candidateTokens
     */
    private function overlapScore(array $questionTokens, array $candidateTokens): float
    {
        if ($questionTokens === [] || $candidateTokens === []) {
            return 0.0;
        }

        $intersection = array_values(array_intersect($questionTokens, $candidateTokens));
        $hits = count($intersection);
        $questionIntentTokens = array_values(array_intersect($questionTokens, $this->intentTokens));
        $intentMatched = count(array_intersect($questionIntentTokens, $candidateTokens)) > 0;

        if (count($questionTokens) >= 3 && $hits < 2 && ! $intentMatched) {
            return 0.0;
        }
        if (count($questionTokens) < 3 && $hits < 1) {
            return 0.0;
        }

        $specificQuestionTokens = array_values(array_diff($questionTokens, $this->entityTokens));
        if ($specificQuestionTokens !== []) {
            $specificHits = array_intersect($specificQuestionTokens, $candidateTokens);
            if (count($specificHits) === 0) {
                return 0.0;
            }
        }

        $score = $hits / count($questionTokens);

        foreach ($questionTokens as $token) {
            if (strlen($token) >= 6 && in_array($token, $candidateTokens, true)) {
                $score += 0.35;
                break;
            }
        }

        $weekdays = ['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
        foreach ($weekdays as $day) {
            if (in_array($day, $questionTokens, true) && in_array($day, $candidateTokens, true)) {
                $score += 0.5;
                break;
            }
        }

        return $score;
    }

    /**
     * @param array<int, string> $questionTokens
     */
    private function isDatabaseQuestion(array $questionTokens): bool
    {
        return in_array('database', $questionTokens, true);
    }

    private function looksLikeDatabaseAnswer(string $snippet): bool
    {
        $tokens = $this->tokenize($snippet);
        $knownDatabaseTerms = [
            'mysql', 'postgresql', 'postgres', 'mariadb', 'sqlite', 'mongodb', 'sqlserver', 'oracle',
        ];

        foreach ($knownDatabaseTerms as $term) {
            if (in_array($term, $tokens, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array{answer: string, sources: array<int, string>, snippets: array<int, string>}
     */
    private function fallbackResponse(): array
    {
        return [
            'answer' => 'I do not have enough information in the provided company documents to answer this question.',
            'sources' => [],
            'snippets' => [],
        ];
    }
}
