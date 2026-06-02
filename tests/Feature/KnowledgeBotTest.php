<?php

namespace Tests\Feature;

use Tests\TestCase;

class KnowledgeBotTest extends TestCase
{
    public function test_it_returns_grounded_answer_for_known_question_from_web_endpoint(): void
    {
        $response = $this->postJson('/ask', [
            'question' => 'What is ParsCRM?',
        ]);

        $response
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => ['question', 'answer', 'sources', 'snippets'],
                'meta' => ['grounded', 'fallback', 'trace_id'],
            ])
            ->assertJsonPath('success', true)
            ->assertJsonPath('meta.grounded', true)
            ->assertJsonPath('meta.fallback', false);
        $this->assertMatchesRegularExpression(
            '/^[0-9a-fA-F-]{36}$/',
            (string) $response->json('meta.trace_id')
        );

        $this->assertStringContainsStringIgnoringCase(
            'crm',
            $response->json('data.answer', '')
        );
        $this->assertNotEmpty($response->json('data.sources', []));
    }

    public function test_it_returns_fallback_for_unknown_question_from_v1_api_endpoint(): void
    {
        $response = $this->postJson('/api/v1/knowledge/ask', [
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

    public function test_it_returns_structured_validation_error(): void
    {
        $response = $this->postJson('/api/v1/knowledge/ask', [
            'question' => 'hi',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('error.code', 'VALIDATION_FAILED')
            ->assertJsonStructure([
                'success',
                'error' => ['code', 'message', 'details'],
            ]);
    }
}
