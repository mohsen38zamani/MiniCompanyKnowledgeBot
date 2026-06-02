<?php

namespace Tests\Feature;

use App\Services\Knowledge\DocumentLoader;
use App\Services\Knowledge\TextChunker;
use Tests\TestCase;

class KnowledgeServicesTest extends TestCase
{
    public function test_document_loader_reads_only_supported_non_empty_docs(): void
    {
        $loader = app(DocumentLoader::class);
        $documents = $loader->loadFromDocsDirectory();

        $titles = array_column($documents, 'title');
        sort($titles);

        $this->assertSame(['faq', 'product-introduction', 'support-guide'], $titles);
        $this->assertTrue(collect($documents)->every(fn (array $doc) => trim($doc['content']) !== ''));
    }

    public function test_chunker_splits_long_lines_within_limit(): void
    {
        $chunker = new TextChunker();
        $documents = [[
            'title' => 'long-text',
            'content' => str_repeat('word ', 150),
        ]];

        $chunks = $chunker->chunk($documents, 80);

        $this->assertNotEmpty($chunks);
        $this->assertTrue(collect($chunks)->every(fn (array $chunk) => mb_strlen($chunk['text']) <= 80));
    }
}
