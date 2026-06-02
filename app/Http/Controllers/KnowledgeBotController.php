<?php

namespace App\Http\Controllers;

use App\Http\Requests\AskKnowledgeQuestionRequest;
use App\Services\Knowledge\AnswerService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class KnowledgeBotController extends Controller
{
    public function index(): View
    {
        return view('knowledge-bot');
    }

    public function ask(AskKnowledgeQuestionRequest $request, AnswerService $answerService): JsonResponse
    {
        $validated = $request->validated();

        $result = $answerService->answer($validated['question']);
        $isFallback = $result['sources'] === [];

        return response()->json([
            'success' => true,
            'data' => [
                'question' => $validated['question'],
                'answer' => $result['answer'],
                'sources' => $result['sources'],
                'snippets' => $result['snippets'],
            ],
            'meta' => [
                'grounded' => ! $isFallback,
                'fallback' => $isFallback,
            ],
        ]);
    }
}
