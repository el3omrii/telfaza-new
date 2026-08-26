<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class ScraperController extends Controller
{
    function getGlwizStreamUrl(String $channelName)
    {
        if (empty($channelName)) {
            return response('Channel name is required.', 400);
        }
        $url = 'https://www.glwiz.com/Pages/Player/Ajax.aspx';

        // Query parameters from the cURL request
        $queryParams = [
            'action' => 'getStreamURL',
            'ClusterName' => 'zixi-hls-box-GLTurk',
            'RecType' => 4,
            'itemName' => $channelName . "_HD",
            'ScreenMode' => 0,
        ];

        // Headers from the cURL request (-H)
        $headers = [
            'accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
            'accept-language' => 'fr-FR,fr;q=0.9,en-US;q=0.8,en;q=0.7,ar;q=0.6',
            'cache-control' => 'max-age=0',
            'priority' => 'u=0, i',
            'sec-ch-ua' => '"Not;A=Brand";v="8", "Chromium";v="150", "Google Chrome";v="150"',
            'sec-ch-ua-mobile' => '?0',
            'sec-ch-ua-platform' => '"Windows"',
            'sec-fetch-dest' => 'document',
            'sec-fetch-mode' => 'navigate',
            'sec-fetch-site' => 'none',
            'sec-fetch-user' => '?1',
            'upgrade-insecure-requests' => '1',
            'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',
        ];

        // Cookies from the cURL request (-b)
        $cookies = [
            'ASP.NET_SessionId' => 'tc2zfkduh3wgli2bggqpr2hi', 
            'SelectedPackage' => 'Farsi', 
            'ISOCode' => 'FA-EN', 
            'LanguageCode' => 'EN', 
            'PackageCode' => 'FA', 
            'SelectedLanguage' => 'SecondLanguage'
        ];
		$body = Cache::remember("glwiz:$channelName", 3600, function () use ($headers, $cookies, $url, $queryParams) {
	        // Reactivate session ID cookie if it has expired
	        Http::withHeaders($headers)->withCookies($cookies, 'www.glwiz.com')
	            ->get('https://www.glwiz.com/Pages/Player/Player.aspx');
	
	        // Send the request
	        $response = Http::withHeaders($headers)
	            ->withCookies($cookies, 'www.glwiz.com')
	            ->get($url, $queryParams);
			return $response->body();
		});
        // The response is JSON formatted but sent with a text/html content type.
        // We manually decode the body to extract the array.
        $data = json_decode($body, true);
		
        // Check if decoding was successful and the 'resp' key exists
        if (json_last_error() === JSON_ERROR_NONE && isset($data['resp'])) {
            $streamUrl = $data['resp'];

            // Use regex to insert "_HD" right before the ".m3u8" part
            // It looks ahead for ".m3u8" followed by either a "?" (query string) or the end of the string
            $streamUrl = preg_replace('/(?=\.m3u8(?:\?|$))/', '_HD', $streamUrl);

            // Return just the modified stream URL as a plain text response
            return response($streamUrl, 200, ['Content-Type' => 'text/plain', 'X-Manifest-URL' => $streamUrl]);
        }

        // Fallback if the API changes or the session cookie expires
        return response('Failed to fetch or parse stream URL.', 500);
    }
    function getSaudiaStreamUrl(String $channelName)
    {
        if (empty($channelName)) {
            return response('Channel name is required.', 400);
        }
        $url = "https://aloula.faulio.com/api/v1.1/channels/$channelName/player";

        // Headers from the cURL request (-H)
        $headers = [
            'accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
            'accept-language' => 'fr-FR,fr;q=0.9,en-US;q=0.8,en;q=0.7,ar;q=0.6',
            'cache-control' => 'max-age=0',
            'priority' => 'u=0, i',
            'sec-ch-ua' => '"Not;A=Brand";v="8", "Chromium";v="150", "Google Chrome";v="150"',
            'sec-ch-ua-mobile' => '?0',
            'sec-ch-ua-platform' => '"Windows"',
            'sec-fetch-dest' => 'document',
            'sec-fetch-mode' => 'navigate',
            'sec-fetch-site' => 'none',
            'sec-fetch-user' => '?1',
            'upgrade-insecure-requests' => '1',
            'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',
        ];

        // Send the request
        $response = Http::withHeaders($headers)
            ->get($url);
		
        // The response is JSON formatted but sent with a text/html content type.
        // We manually decode the body to extract the array.
        $data = json_decode($response->body(), true);
        // Check if decoding was successful and the 'resp' key exists
        if (json_last_error() === JSON_ERROR_NONE) {
            $stream_url = $data['streams']['hls'];
            if ($stream_url)
                return response()->json(["stream_url" => $stream_url]);
        }

        // Fallback if the API changes or the session cookie expires
        return response('Failed to fetch or parse stream URL.', 500);
    }

    function getSumariaStreamUrl()
    {
        $url = 'https://www.alsumaria.tv/Live/video';

        // Headers from the cURL request (-H)
        $headers = [
            'accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
            'accept-language' => 'fr-FR,fr;q=0.9,en-US;q=0.8,en;q=0.7,ar;q=0.6',
            'cache-control' => 'max-age=0',
            'priority' => 'u=0, i',
            'sec-ch-ua' => '"Not;A=Brand";v="8", "Chromium";v="150", "Google Chrome";v="150"',
            'referer' => 'https://www.alsumaria.tv/',
            'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',
        ];

        // Cookies from the cURL request (-b)
        $cookies = [
            'ASP.NET_SessionId' => 'z2mguhsm30dg22nbdugggeam', 
            'otmDataFrontend' => 'clsGlobals_LanguageID=1',
        ];
		//$stream_url = Cache::remember("sumaria", 3600, function () use ($headers, $cookies, $url) {
	        // Reactivate session ID cookie if it has expired
	        Http::withHeaders($headers)->withCookies($cookies, 'www.alsumaria.tv')
	            ->get('https://www.alsumaria.tv/');
	
	        // Send the request
	        $response = Http::withHeaders($headers)
	            ->withCookies($cookies, 'www.alsumaria.tv')
	            ->get($url);
            // preg_match
            $matches = [];
            $match = preg_match_all('/file: "(.*)"/', $response->body(), $matches);
			$stream_url = $matches[1][0];
        if ($stream_url)
            return response()->json(["stream_url" => $stream_url]);

        // Fallback if the API changes or the session cookie expires
        return response('Failed to fetch or parse stream URL.', 500);
    }

    function getAlkassStreamUrl(int $channelName)
    {
        if (empty($channelName)) {
            return response('Channel name is required.', 400);
        }
        $url = 'https://shoofapi.alkass.net/Shoof/liveV3.php';

        // Headers from the cURL request (-H)
        $headers = [
            'accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
            'accept-language' => 'fr-FR,fr;q=0.9,en-US;q=0.8,en;q=0.7,ar;q=0.6',
            'authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJuYW1laWQiOiIxMzcwMjMwIiwibmJmIjoxNzg3Njk3NzgzLCJleHAiOjE4MDMyNDk3ODMsImlhdCI6MTc4NzY5Nzc4MywiaXNzIjoiaHR0cDovL2Fsa2Fzcy5uZXQiLCJhdWQiOiJodHRwOi8vYWxrYXNzLm5ldCJ9.m7ZgiMSzq9URPYKo30dFnFdxXikPuAhb14qIbrYiJoY',
            'cache-control' => 'max-age=0',
            'priority' => 'u=0, i',
            'sec-ch-ua' => '"Not;A=Brand";v="8", "Chromium";v="150", "Google Chrome";v="150"',
            'origin' => 'https://shoof.alkass.net',
            'referer' => 'https://shoof.alkass.net/',
            'sec-ch-ua-mobile' => '?0',
            'sec-ch-ua-platform' => '"Windows"',
            'sec-fetch-dest' => 'document',
            'sec-fetch-mode' => 'navigate',
            'sec-fetch-site' => 'none',
            'sec-fetch-user' => '?1',
            'upgrade-insecure-requests' => '1',
            'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',
        ];

		$body = Cache::remember("alkassapibody", 300, function () use ($headers, $url) {
	        // Send the request
	        $response = Http::withHeaders($headers)->get($url);
            return $response->body();
        });
        $data = json_decode($body, true);
        $item = collect($data)->firstWhere('id', $channelName);
        $stream_url = $item['body'] ?? null;
        if ($stream_url)
            return response($stream_url, 200, ['Content-Type' => 'text/plain', 'X-Manifest-URL' => $stream_url]);

        // Fallback if the API changes or the session cookie expires
        return response('Failed to fetch or parse stream URL.', 500);
    }

}
