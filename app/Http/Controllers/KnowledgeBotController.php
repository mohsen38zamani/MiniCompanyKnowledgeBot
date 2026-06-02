<?php

namespace App\Http\Controllers;

use App\Http\Requests\AskKnowledgeQuestionRequest;
use App\Services\Knowledge\AnswerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
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
        $traceId = (string) Str::uuid();

        $result = $answerService->answer($validated['question']);
        $isFallback = $result['sources'] === [];

        Log::channel('knowledgebot')->info('Knowledge bot question answered.', [
            'trace_id' => $traceId,
            'question' => $validated['question'],
            'answer' => $result['answer'],
            'sources' => $result['sources'],
            'snippets' => $result['snippets'],
            'grounded' => ! $isFallback,
            'fallback' => $isFallback,
            'endpoint' => $request->path(),
            'client_ip' => $request->ip(),
        ]);

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
                'trace_id' => $traceId,
            ],
        ]);
    }
}
