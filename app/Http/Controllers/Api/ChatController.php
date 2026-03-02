<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Knowledge;
use Illuminate\Support\Facades\Http;

class ChatController extends Controller
{
    public function chat(Request $request)
    {
        $request->validate([
            'question' => 'required|string',
            'type' => 'required|string'
        ]);

        $question = $request->question;
        $type = $request->type;

        $knowledgeChunks = Knowledge::where('type', $type)
            ->where('content', 'LIKE', "%{$question}%")
            ->limit(5)
            ->pluck('content')
            ->implode("\n\n");
            
        if (!$knowledgeChunks) {
            $knowledgeChunks = Knowledge::where('type', $type)
                ->limit(5)
                ->pluck('content')
                ->implode("\n\n");
        }
        
        $prompt = $this->buildPrompt($knowledgeChunks, $question);
        $response = Http::post(
            "https://generativelanguage.googleapis.com/v1/models/gemini-2.5-flash:generateContent?key=" . env('GEMINI_API_KEY'),
            [
                "contents" => [
                    [
                        "parts" => [
                            ["text" => $prompt]
                        ]
                    ]
                ]
            ]
        );

        $reply = $response->json('candidates.0.content.parts.0.text');

        return response()->json([
            'reply' => $reply ?? '<div><p>No response generated.</p></div>'
        ]);
    }

    private function buildPrompt($context, $question)
    {
        return "
        You are a professional assistant answering based strictly on the provided knowledge.

        Rules:
        - Always answer clearly.
        - If procedural, use ordered steps.
        - Return valid HTML inside a single <div>.
        - Use <p>, <ol>, <ul>, <li>, <strong>.
        - No markdown.

        KNOWLEDGE:
        {$context}

        QUESTION:
        {$question}
        ";
    }
}