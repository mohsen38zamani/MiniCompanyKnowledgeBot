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

        foreach (File::files($docsPath) as $file) {
            $documents[] = [
                'title' => pathinfo($file->getFilename(), PATHINFO_FILENAME),
                'content' => trim(File::get($file->getPathname())),
            ];
        }

        return $documents;
    }
}
