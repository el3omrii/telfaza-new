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
            ->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}", [
                'contents' => [[
                    'parts' => [['text' => $prompt]],
                ]],
                'generationConfig' => [
                    'temperature'     => 0.7,
                    //'maxOutputTokens' => 300,
				    'responseMimeType' => 'text/plain',
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
        Act as an expert SEO Copywriter specializing in media and live streaming websites. 
        Your task is to write the SEO Meta Data and On-Page Content for a live TV streaming page.
        - Channel name: {$name}
        - Target url : https://telfazalive.com/channels/{$name}-live-streaming-online
        - Target Keyword: [{$name} tv live streaming online]
        {$context}

        STRICT GUIDELINES YOU MUST FOLLOW:
        1. SEARCH INTENT: The user's intent is ACTION-ORIENTED. They want to watch a stream RIGHT NOW. Do not write a history lesson or a Wikipedia-style "About Us" page. 
        2. CONTENT STRUCTURE:
        - H1: Include the target keyword naturally.
        - H2: "How to Watch [Channel Name] Live Online" (Keep it brief, 2-3 sentences focusing on clicking the player).
        - H2: "What You Can Expect on [Channel Name]" (Bullet points of the top 3-4 types of shows/music they will see. Use the Secondary Keywords here).
        - H2: "Why Stream [Channel Name] on Telfaza Live?" (Brief section on quality, no buffering, free access, etc.)
        5. FORMATTING: Keep paragraphs very short (2-3 sentences max). Use bullet points. This is for mobile users who scan text.
        6. TONE: Energetic, direct, and helpful. 
        7. DO NOT include satellite frequencies, Biss keys, or technical dish settings. 


        Return only the description text, nothing else.
        PROMPT;
		/*return "Write a concise, brief, professional description for a TV channel. "
         . "Details: {$name}" . ($context ? " ({$context})" : "") . ". "
		 . "Start with channel history, satellite, frequency " 
         . "Focus on content and audience. Use keywords like live and online. "
         . "Plain text only. Return only the description.";*/
    }
}