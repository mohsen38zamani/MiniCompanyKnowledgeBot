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
            ->assertJsonStructure([
                'success',
                'data' => ['question', 'answer', 'sources', 'snippets'],
                'meta' => ['grounded', 'fallback'],
            ])
            ->assertJsonPath('success', true)
            ->assertJsonPath('meta.grounded', true)
            ->assertJsonPath('meta.fallback', false);

        $this->assertStringContainsStringIgnoringCase(
            'crm',
            $response->json('data.answer', '')
        );
        $this->assertNotEmpty($response->json('data.sources', []));
    }

    public function test_it_returns_fallback_for_unknown_question(): void
    {
        $response = $this->postJson('/ask', [
            'question' => 'What is the moon phase policy?',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('meta.grounded', false)
            ->assertJsonPath('meta.fallback', true)
            ->assertJsonPath(
                'data.answer',
                'I do not have enough information in the provided company documents to answer this question.'
            );
    }
}
