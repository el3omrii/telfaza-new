<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GeminiController extends Controller
{
    public function generateDescription(Request $request): JsonResponse
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'category' => 'nullable|string',
            'language' => 'nullable|string',
            'country'  => 'nullable|string',
        ]);

        $apiKey = config('services.gemini.key');

        if (!$apiKey) {
            return response()->json(['error' => 'Gemini API key not configured.'], 500);
        }

        $prompt = $this->buildPrompt(
            $request->name,
            $request->category,
            $request->language,
            $request->country,
        );

        $response = Http::withHeaders(['Content-Type' => 'application/json'])
            ->timeout(15)
            ->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key={$apiKey}", [
                'contents' => [[
                    'parts' => [['text' => $prompt]],
                ]],
                'generationConfig' => [
                    'temperature'     => 0.7,
                    'maxOutputTokens' => 150,
                ],
            ]);

        if ($response->failed()) {
            return response()->json(['error' => 'Gemini request failed. Please try again.'], 502);
        }

        $text = $response->json('candidates.0.content.parts.0.text');

        if (!$text) {
            return response()->json(['error' => 'No content returned from Gemini.'], 502);
        }

        return response()->json(['description' => trim($text)]);
    }

    private function buildPrompt(string $name, ?string $category, ?string $language, ?string $country): string
    {
        $context = collect([
            $category ? "Category: {$category}" : null,
            $language ? "Language: {$language}" : null,
            $country  ? "Country: {$country}"   : null,
        ])->filter()->implode(', ');

        return <<<PROMPT
        Write a concise, engaging description for a TV/streaming channel with the following details:
        Channel name: {$name}
        {$context}

        Requirements:
        - 2-3 sentences maximum
        - Professional and informative tone
        - Focus on the channel's content and audience
        - Do not include the channel name at the start of the description
        - Plain text only, no markdown or bullet points

        Return only the description text, nothing else.
        PROMPT;
    }
}