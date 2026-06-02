<?php

namespace Tests\Feature;

use Tests\TestCase;

class KnowledgeBotTest extends TestCase
{
    public function test_it_returns_grounded_answer_for_known_question(): void
    {
        $response = $this->postJson('/ask', [
            'question' => 'What is ParsCRM?',
        ]);

        $response
            ->assertOk()
            ->assertJsonStructure(['answer', 'sources', 'snippets']);

        $this->assertStringContainsStringIgnoringCase(
            'crm',
            $response->json('answer', '')
        );
        $this->assertNotEmpty($response->json('sources', []));
    }

    public function test_it_returns_fallback_for_unknown_question(): void
    {
        $response = $this->postJson('/ask', [
            'question' => 'What is the moon phase policy?',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath(
                'answer',
                'I do not have enough information in the provided company documents to answer this question.'
            );
    }
}
