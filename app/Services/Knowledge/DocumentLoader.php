<?php

namespace App\Services\Knowledge;

use Illuminate\Support\Facades\File;

class DocumentLoader
{
    /**
     * @return array<int, array{title: string, content: string}>
     */
    public function loadFromDocsDirectory(): array
    {
        $docsPath = base_path('docs');

        if (! File::isDirectory($docsPath)) {
            return [];
        }

        $documents = [];

        $files = collect(File::files($docsPath))
            ->filter(function (\SplFileInfo $file): bool {
                $extension = mb_strtolower($file->getExtension());

                return in_array($extension, ['txt', 'md'], true);
            })
            ->sortBy(fn (\SplFileInfo $file) => $file->getFilename())
            ->values();

        foreach ($files as $file) {
            $content = trim(File::get($file->getPathname()));
            if ($content === '') {
                continue;
            }

            $documents[] = [
                'title' => pathinfo($file->getFilename(), PATHINFO_FILENAME),
                'content' => $content,
            ];
        }

        return $documents;
    }
}
