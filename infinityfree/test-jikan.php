<?php
// Jikan API connectivity test — delete this file after confirming it works on InfinityFree

$url = 'https://api.jikan.moe/v4/seasons/now?limit=5';

if (!function_exists('curl_init')) {
    die('<b>cURL is not available on this server.</b>');
}

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 15,
    CURLOPT_USERAGENT      => 'AniTrack/1.0',
    CURLOPT_SSL_VERIFYPEER => false,
]);
$body = curl_exec($ch);
$err  = curl_error($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo '<pre style="font-family:monospace;font-size:14px">';
if ($err) {
    echo "cURL Error: $err\n";
} elseif ($code !== 200) {
    echo "HTTP $code — Response:\n" . htmlspecialchars(substr($body, 0, 500));
} else {
    $data = json_decode($body, true);
    if ($data && isset($data['data'])) {
        echo "✓ Connected to Jikan API!\n";
        echo "Got " . count($data['data']) . " anime this season:\n\n";
        foreach ($data['data'] as $a) {
            echo "  - " . $a['title'] . " (score: " . ($a['score'] ?? 'N/A') . ")\n";
        }
    } else {
        echo "Unexpected response:\n" . htmlspecialchars(substr($body, 0, 500));
    }
}
echo '</pre>';
