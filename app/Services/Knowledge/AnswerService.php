<?php

namespace App\Services\Knowledge;

class AnswerService
{
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
        $chunks = $this->textChunker->chunk($documents);
        $matches = $this->keywordRetriever->topRelevantChunks($question, $chunks);

        if ($matches === []) {
            return [
                'answer' => 'I do not have enough information in the provided company documents to answer this question.',
                'sources' => [],
                'snippets' => [],
            ];
        }

        $snippets = array_map(
            fn (array $match) => $match['text'],
            $matches
        );
        $sources = array_values(array_unique(array_map(
            fn (array $match) => $match['source'],
            $matches
        )));

        return [
            'answer' => implode(' ', $snippets),
            'sources' => $sources,
            'snippets' => $snippets,
        ];
    }
}
