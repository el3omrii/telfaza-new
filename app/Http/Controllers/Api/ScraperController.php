<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;
use RuntimeException;

class ScraperController extends Controller
{
	private function decrypt(string $payload): array
    {
		$KEY = '43fc9b1a3f854049'; // AES-128 key from the web app
        [$ct, $iv] = explode(':', trim($payload), 2);
        $pt = openssl_decrypt(base64_decode($ct), 'AES-128-CBC', $KEY,
                              OPENSSL_RAW_DATA, base64_decode($iv));
        if ($pt === false) {
            throw new RuntimeException('DramaLive: failed to decrypt response');
        }
        return json_decode($pt, true) ?? [];
    }

	function getDramaliveStreamUrl(string $channelName) {

    	// Exact /glbr URL from your browser capture
    	$GLBR_URL = 'https://re.highperformanceconnect.shop/redirect/glbr';

	    /**
	     * Channel name => captured glbr request payload (verbatim from devtools).
	     * Add a channel by capturing its /glbr call and adding one line.
	     */
	    $PAYLOADS = [
	        'ws' => 'tM4VlTLZtbfdoD6sbhfaFAcf57+tBTjgroGZTG5B2WWgNCKipbYoSxtTENYKZumc+iPMmCr5NsOHmspdJxjc85jHIjJSDJFCnDbvpkkHmVxVGUqACMf3LRPnHYxkjVWwAwOw+vO5XSnYCdpUyN28EdocCmwaqgOJOsqIJh7XK9lU9sPZft7UGKI2nBiQaXFl9KaW8KNBZCmZs+ijOAHHITgLqCwGPEpBD3NWcuIS7/NUJF30TUWpUgf1kvyuFI3xEIpgqDPXUBfbhoHeQkXXDdz5hiS9Sqx6VgROkhALfwkNyTzgxMj/5EEZpEb2kHi5kLX9hHcz9HtmYtYi7XXFdnitswvwD7pTt1hZAQyHIAXXhvc9DsobxK7xsroRd3Qq+BsfyOE9OCSE4qtypLs3A/4U9naHPRRG4xRu46usuRGGhum79dPVGzM0gwXTgDs3I2jiYYEEqZ3ygxU1ndhQQx9KKUG68GEmP5vJDsZt1TJN8dsDIQAMSdgRm3pV5BsEU/dTYd9TenEuNAr/tTGaOBAtWgPWn2WFCR3qDq97fp4Xkms4An5qVo4q9+0RGoNsUO4Gbpv5zsDckQxyjqOR2g2ENtbyVjyfC5PqM/8GdR8hySvbyJrfEU59n1vW9RfqSmOJargBGvXReYLt3zj3a5xHDlknSWFocaKuz1evWQqIXIzpBqYg2Vw3/W5j7L5OVeWg20n3cwHWKcDlrCpJDksl3yzNVWH4kksHGnRMpCKXCJdRUvnyqZXPT4QjCriooWNFDwg1SyIlZl6+BAvDXMrLekAF5Pf5NE53IFnuZNhynLOXTDrx3NziYrXmRoWF7HTOl4LfOlc3Xw8qXY6uStpNk6E8VuHDrI8vMovZRFQdRc5wnGqR4bhXUlsJxgBRdQ0ksrTKV7tDRY1S80aqh4U2GUMmSmk/0GlDzKoXoY8=:FLDKZgIZVVWkxvvokEA5Ww==',
	        'hiwar' => '5XRaqpzq9M4tInoxp5k/a+igqh/5qCg5PeLP6ZFvoXXV22DtQmyxvSSwQVFnLj8XqpFPJfesQhBSnjMTcPxll0vKySRmLS60IIP+IRHTkWqttotD0Ga0gzON7g5rO03Ab18pJUf8rXqjeH4i4Rb/KFc9FW3JMR4bPtPF2Trhlg81oZNabhFBgLkWYXFmPtx1mTzn5vgVT3RhVY8UbV5DKtnFqD9TFmCyQyH9TiVdjmVMJF0jyBQHN6iQSCQ2j0sK9yuKbkcCgLUXk+fbyGsQh26D6OgS+n64jGW7oix8BYck/F931/ZLuyEJDkF+i99IQjGRFh9UgUrEQcvKim3PjnicoPsjlfA8YtRRClSdNfXyfxL4s9sCttTcQy/INoeaNt/gi8O5Si/t5iqcLgD5/d8Piqwa98PxwAKLivd6npsCWAvYcdoAoXyK/B2pcq2oVMQd/zwXj1L1C8FqbB+oX2et2c2UkQSw5I7fzHe2DBh5eu7Vu9YFCbWJg5E7lgiE7ROmDoKNUiPI5AmXE2oix3rCiuGh/upaexWvE6PIcb6o5bntJ3wcSp3W5GbdOiJmpHv4We5KmlIzWG7rNgDyoglzhhg5PQP4QgxzrdIV3SmFejbf+y+BAFrbJsvgGH5VFYFeQJNwvEbpFLalMWvuW+odbhjFIAZmT++kbHcX/CM+2QAPgX1rEMfUJ9fHd/13Kz4ZU7trpqNPCQiiaZr7ZULKbVKxoFue1vyzC0ilZxbwto918dTqJhDQs0mSdGKOfyXy7g1+9aHRT7I1sT/hv9+J6Omx/MnNgA0xB+nTSN+nmTkNg8kFMSlKIvIbqnSE5Z7DBDBqUu4tAXsEPLRRUka8CFI6mlmlCAraDLv6bHAuk+oX16lWuJQLxt0/RXmz:kLhUKzZ3Fm0RVxBv94/ouQ==',
			'samira' => '/UkKh+ZjkHhF1tw5smlcN5owdY7FogkuB6tyrhrihedR+wtqV+WrtbA3NfWGt4kddX8ruTnGxJyMmwGUxUg6dqsDHG9g+X2Sg2xhRlLt5KI6+A50SdHQmGcANVy1M8HKQmfLDw9/h50GgQ0DE34t2XKpN23BUlfWWn7wwLiRDL4qSWNSKkwpeqJgI5//zXyYmh7y85SbGrWDmQ4Y2g9Iple6HrAq51xC4oapDTTGK4nhGdbTWi3ZSoL3ua3oX5lgC+vG/2ljTfJ7k4eGodNhDAsmCew6kVPq9UiUKnnGxGVEIOjiF+qdXVkJT/MAOq+Am7+97/TQ74mFTfR/4rc5mBkT52N5W97bNt0qZpwfnh+PScjn+uzTs/02YIIEJlKu/sKUuw36WpsTu9XkoBzMRTFz6mSqs/eMOWwfw1nsb9goJhZs3VJJwPMi0Myrteth48fF0zIRsH6uE+7x55D+TjTJDeT6B35YTgQl9XHtVFJdEffdab0Ux5VJNyie9FE+sGanBO287Q4j9B1+rY3UFNca3fMz6wxwA3rW08VPzwSjHFaSasQshqTC3JwhiLh1XiCxbDHvfu/TwlkzWspmpIorvRm7Xtzg4kH8s/nJMcciFseu/gpQjkatlYC2YSuQjA6hZ11b8dmstlGnnNhhOatnxpXNVww9+dIyT7zUctyEwMW/KtQHPbSmPIVKwcF24/wJxoD4FkTI3GjP4S0+lo4Zds/om4ffywMkwYCUz7hNYQKkzVzyz7mrbEkJ8QCVCXU/xZrD+AAiTlydRawSrkhVacHQXC/EstB/1E9ZCNhMqrG3tBWJKQF0+oO4dkK0+M2z5p2/73LJQXKjf3Et/lrmLaCo0Ky4bcPnecpq0yC7mZY0zVHFp/jZ9fLRxFIa:cChYC6b1FOnR/EjMXtLsuQ==',
			'fadjr' => '4F8mWgT5xOzzcNdXnAxYU8gR58Qpy1D7qIjx8F2gQfGuS5Eds1iwaUIiWa3NRerAOuFAfrPjiPL8M4Pfd9asuRhNhrr4/UcqBd5VKemFb7FBXaDPJ40wzRF3Qdn/jHBbPi4YpTTdfcWZM/EdtQ0v4/ozGUzuQIsQsnubbhs9LyouLD9d80BiwsTx5orLGg//ZhE0yPMDnv3L5/12uvna46XkEIrfbPrgcFHxzsvvCmh+1Fglynt87GliqJS1o11i3wxycgtFkAPKgBr1kL8c5QnNAtBTPrvz6WavMLU+72ZdYB6/wHNNkHQ6Xw5S23nlGYE2rYzmpWGOgFBdoG7UEUWlvMaLLxhnDmx53iUKl+rmvHWBbBlXShMraHKPGWa9Dt8wtXLcValGyw6QcRgQwvMQwaU82KWobnQq5nFZaULF/WVyunk96OC8qSEiNLqwqb4l3o1WtFK5Sfnm57ct1shnRLSj8slGGzGcG3lsDLF7sAxZ1BjukSfbIAd+8dIPrKEiGPUO2Cj4MMl61G3BIs/VMR+jYJKcgxQ+SwAESkLQkYZFQi9OwbY6jgzZ2fLQOOwb4RQcajX52CM3prqIM2i78vd5SyTsp/LcRiHnAp6gdt/iot5v+5k0S+OoqbBkjqpa/a9k2SEwxxOjqXGZsFq7naLUxs1N2wcgEddWFHEhtcnKHlpX23Z/ym8FpPINn6M5LXePLDDw8bIzcArsgtDjxfy3oeYs0IK02+DQZ7EXUvU3uMaWZjy/c1fX2Xt3Y1ria/Ln0q1y2v7FHlyktlH+he77EpSucVgZkM00QRHcqRSPgl+dN07yR7KGXcUZwD5J5cYNfRG8+CunWIATxxMIPGmFHM6dzDGw3uS9Tjy9i4Cz/wwI2W2VriiaunEXRvPf3v1vhlxQ7NM68MLHxQ==:3iM3S1sOsnyPHZ6gDPk8tg==',
			'ennahar' => 'wVUcxDxVTLq/2CxeiXyXqGMmUOypVi1omyxcfkHrnyavSSiGo6NWWNlefx8yl1QjlzAk1oOzYDBr1dMUkbRiST6OOzl1JDKFnLlwuR5ukAbY7WWIYhk5sNXMFZ++GMUaf2Rny654c0Tw+agyiU8aO8T5ae28RX837UQot9rqFvWst27CUxFRFpt8bu6nvxZVmwIsg5G+Ztaydnm4Syx8plXukYPvXdR8DHoVq4T5n28hpRo5CYsHVdVrE2wc+e3Y2V2EXJvL+7TfRgIok6SiEBNHPKAppbxOfcNakK5tNlT3t4jMyP8xPGXqKeGi/DVZ/a6NjhjRrNtNkpvI/znHACj0Ank4AvRajmqV5tGoksjZFpyHcepKSpZ0+gjh2YDHfUm1N9FA/JbirvKV33pqPTuRNmhFFhbYI+rXYmNcJDSzmTJXUYS7NIS4y1dC7lSY5k/C+dkuEH94eHpFHT/+1nNgk3X827QShmMq3DoaQScWaboZh0ICzLLjB4eGTapaq0ClaTcsfvb1Gx562/BwQRKm33ZoPJSJcd4xoFGYcgNRAb04/c+qWlbxEuz54p72vpinukTs2o/hNvkLhAw7LZSwxTvHT4Iq0ihvXQtIJ6/9XZdKxxhV5mo7MkgUNNHSJTDM+T9/2Ca7pLMZ54VtU7FU6mlu48N7eEDzVLxPHGEj0WnuZdC8qOG9xP8TgQpSe+Wgn1fg+YttOvGo2TN8QyKI8InN8DGcfYNabzmPOeqVKEeLK0265ucoqIO8vO45Zo6KBojZ6BjmatL8eO0ymnivFgPEWEXJHJxH5SBZkF2GQT4WNStQdkthUQrwnZcnJBZQ56X/SWU0AvtWGR197BMOI4kZi+e4BauFdK0RvAd9WQ6HcsZcAMHUByHJPjaDXmYCxxYFzFSvRcVX1O6J4A==:/13BvIXUBP8UJFJ6lVpMsg==',
		];
		$payload = $PAYLOADS[$channelName]
            ?? throw new InvalidArgumentException("DramaLive: unknown channel '{$channelName}'");

        $response = Http::withHeaders(['Content-Type' => 'text/plain', 'Accept' => '*/*', 'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36'])
            ->withBody($payload, 'text/plain')
            ->post($GLBR_URL);

        if (!$response->successful()) {
            throw new RuntimeException("DramaLive: glbr HTTP {$response->status()} for '{$channelName}'");
        }
		$data = $this->decrypt($response->body())['data'] ?? [];

        // agent "advanced": data.url is a JSON string with the real stream config
        $cfg = json_decode($data['url'] ?? '', true);
        if (!is_array($cfg)) {
            return response($data['url'], 200, ['Content-Type' => 'text/plain', 'X-Manifest-URL' => $data['url']]);
        }

		return response($cfg['url'], 200, ['Content-Type' => 'text/plain', 'X-Manifest-URL' => $cfg['url']]);
	}
    function getGlwizStreamUrl(string $channelName)
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
    function getSaudiaStreamUrl(string $channelName)
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
			"Origin" => "https://aloula.sba.sa",
            "Referer" => "https://aloula.sba.sa/",
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
                return response()->json(["stream_url" => $stream_url], 200, ['X-Manifest-URL' => $stream_url]);
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
    
    function getFranceStreamUrl(string $channel) 
    {
        if ($channel === "tv5monde") {
			$streamUrl = "https://raw.githubusercontent.com/Paradise-91/ParaTV/main/streams/francetv/v7.m3u8";
			return response($streamUrl, 200, ['Content-Type' => 'text/plain', 'X-Manifest-URL' => $streamUrl]);
		}
        $m3uUrl = 'https://raw.githubusercontent.com/Paradise-91/ParaTV/refs/heads/main/playlists/paratv/group/france/france.m3u';

        // Pro Tip: Cache the result for 1 hour to avoid hitting the remote server on every request
        $streamUrl = null;
        $response = Http::timeout(10)->get($m3uUrl);

        if ($response->failed()) {
            return null;
        }

		// replace dash by space
		$channel = preg_replace('/[_-]/', ' ', $channel);

        $m3uContent = $response->body();

            // Regex breakdown:
            // #EXTINF              : Matches the literal "#EXTINF"
            // [^\n]*               : Matches any characters except a newline (rest of the EXTINF line)
            // tvg-id="TF1\.fr"     : Specifically targets the TF1.fr channel ID (avoids TMC, TFX, etc.)
            // [^\n]*               : Matches the rest of the line
            // \n                   : Matches the newline character
            // \s*                  : Matches any optional whitespace (spaces/tabs)
            // (https?:\/\/[^\s]+)  : Captures the URL (http/https up to the next whitespace/newline)
        //$pattern = '/#EXTINF[^\n]*'.$channel.'[^\n]*\n\s*(https?:\/\/[^\s]+)/i';
        $pattern = '/#EXTINF[^\n]*'.$channel.'[^\n]*\n\s*(https?:\/\/[^\s]+)/i';

        if (preg_match($pattern, $m3uContent, $matches)) {
            $streamUrl = $matches[1]; // Return the captured URL (Group 1)
        }

        if (!$streamUrl) {
            return response()->json([
                'success' => false,
                'message' => $channel.' stream not found or failed to fetch M3U file.'
            ], 404);
        }

        return response($streamUrl, 200, ['Content-Type' => 'text/plain', 'X-Manifest-URL' => $streamUrl]);
    }
}
