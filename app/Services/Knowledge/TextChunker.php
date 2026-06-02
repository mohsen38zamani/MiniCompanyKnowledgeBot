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

                $parts = $this->splitLongLine($line, $maxChunkLength);
                foreach ($parts as $part) {
                    $candidate = $buffer === '' ? $part : $buffer.' '.$part;

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

                    $buffer = $part;
                }
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

    /**
     * @return array<int, string>
     */
    private function splitLongLine(string $line, int $maxChunkLength): array
    {
        if (mb_strlen($line) <= $maxChunkLength) {
            return [$line];
        }

        $sentences = preg_split('/(?<=[.!?])\s+/', $line) ?: [$line];
        $parts = [];
        $buffer = '';

        foreach ($sentences as $sentence) {
            $sentence = trim($sentence);
            if ($sentence === '') {
                continue;
            }

            $candidate = $buffer === '' ? $sentence : $buffer.' '.$sentence;
            if (mb_strlen($candidate) <= $maxChunkLength) {
                $buffer = $candidate;
                continue;
            }

            if ($buffer !== '') {
                $parts[] = $buffer;
                $buffer = '';
            }

            if (mb_strlen($sentence) <= $maxChunkLength) {
                $buffer = $sentence;
                continue;
            }

            $words = preg_split('/\s+/', $sentence) ?: [$sentence];
            $wordBuffer = '';
            foreach ($words as $word) {
                $candidateWord = $wordBuffer === '' ? $word : $wordBuffer.' '.$word;
                if (mb_strlen($candidateWord) <= $maxChunkLength) {
                    $wordBuffer = $candidateWord;
                    continue;
                }

                if ($wordBuffer !== '') {
                    $parts[] = $wordBuffer;
                }
                $wordBuffer = $word;
            }

            if ($wordBuffer !== '') {
                $parts[] = $wordBuffer;
            }
        }

        if ($buffer !== '') {
            $parts[] = $buffer;
        }

        return $parts === [] ? [$line] : $parts;
    }
}
