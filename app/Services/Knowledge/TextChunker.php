<?php

namespace App\Services\Knowledge;

class TextChunker
{
    /**
     * @param array<int, array{title: string, content: string}> $documents
     * @return array<int, array{source: string, text: string}>
     */
    public function chunk(array $documents, int $maxChunkLength = 300): array
    {
        $chunks = [];

        foreach ($documents as $document) {
            $lines = preg_split('/\R+/', $document['content']) ?: [];
            $buffer = '';

            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '') {
                    continue;
                }

                $candidate = $buffer === '' ? $line : $buffer.' '.$line;

                if (mb_strlen($candidate) <= $maxChunkLength) {
                    $buffer = $candidate;
                    continue;
                }

                if ($buffer !== '') {
                    $chunks[] = [
                        'source' => $document['title'],
                        'text' => $buffer,
                    ];
                }

                $buffer = $line;
            }

            if ($buffer !== '') {
                $chunks[] = [
                    'source' => $document['title'],
                    'text' => $buffer,
                ];
            }
        }

        return $chunks;
    }
}
