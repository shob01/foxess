<?php

declare(strict_types=1);

require $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';

use Dotenv\Dotenv;

class GetAuthor
{
    public function get_signature($_token, $_url, $_lang = 'en')
    {
        $timestamp = round(microtime(true) * 1000);
        $signature = "{$_url}\r\n{$_token}\r\n{$timestamp}";

        $result = [
            'token' => $_token,
            'lang' => $_lang,
            'timestamp' => strval($timestamp),
            'signature' => $this->md5c($signature),
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/117.0.0.0 Safari/537.36',
        ];

        return $result;
    }

    public static function md5c($text = "", $_type = "lower")
    {
        $res = md5($text);
        return ($_type === "lower") ? $res : strtoupper($res);
    }
}

$domain = 'https://www.foxesscloud.com';
$url = '/op/v0/device/real/query';
$request_param = ["variables" => ["pv1Power", "pv2Power"]];

$dotenv = Dotenv::createImmutable($_SERVER['DOCUMENT_ROOT'], '.env');
$dotenv->load();
$key = $_ENV['FOXESS_API_KEY'];

$headers = (new GetAuthor())->get_signature($key, $url);
$fullUrl = $domain . $url;

$ch = curl_init($fullUrl);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request_param));
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);

$status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

curl_close($ch);

echo "status_code: $status_code\n";
echo "content: $response\n";
