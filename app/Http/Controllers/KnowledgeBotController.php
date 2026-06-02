<?php

namespace App\Http\Controllers;

use App\Services\Knowledge\AnswerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KnowledgeBotController extends Controller
{
    public function index(): View
    {
        return view('knowledge-bot');
    }

    public function ask(Request $request, AnswerService $answerService): JsonResponse
    {
        $validated = $request->validate([
            'question' => ['required', 'string', 'min:3', 'max:500'],
        ]);

        $result = $answerService->answer($validated['question']);

        return response()->json($result);
    }
}
