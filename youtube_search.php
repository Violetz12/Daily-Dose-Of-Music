<?php
// youtube_search.php
// Server-side proxy for YouTube Data API v3
// Keeps your API key hidden from the browser

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// ─── PUT YOUR YOUTUBE API KEY HERE ───────────────────────────────────────────
define('YOUTUBE_API_KEY', 'YOUR_YOUTUBE_API_KEY_HERE');
// ─────────────────────────────────────────────────────────────────────────────

$query    = trim($_GET['q'] ?? '');
$maxRes   = min((int)($_GET['max'] ?? 8), 20);

if (!$query) {
    echo json_encode(['error' => 'No query provided']);
    exit;
}

if (YOUTUBE_API_KEY === 'YOUR_YOUTUBE_API_KEY_HERE') {
    // Return mock data if no API key set yet
    $mock = [];
    $mockSongs = [
        ['APT - Bruno Mars & Rosé', 'dQw4w9WgXcQ'],
        ['Die with a Smile - Lady Gaga & Bruno Mars', 'kffacxfA7G4'],
        ['Anti-Hero - Taylor Swift', 'b1kbLwvqugk'],
        ['Pink Venom - BLACKPINK', 'tyQpWCNfxG8'],
        ['MONEY - LISA', 'tyQpWCNfxG8'],
        ['Shake It Off - Taylor Swift', 'nfWlot6h_JM'],
        ['God\'s Menu - Stray Kids', 'TuO_xjPEA7I'],
        ['Bloody Mary - Lady Gaga', 'WCnt7w5xwTs'],
    ];
    foreach (array_slice($mockSongs, 0, $maxRes) as $s) {
        $mock[] = [
            'videoId'     => $s[1],
            'title'       => $s[0],
            'thumbnail'   => "https://img.youtube.com/vi/{$s[1]}/mqdefault.jpg",
            'channelName' => 'Official',
        ];
    }
    echo json_encode(['items' => $mock, 'mock' => true]);
    exit;
}

// Real YouTube API call
$url = 'https://www.googleapis.com/youtube/v3/search?' . http_build_query([
    'part'       => 'snippet',
    'q'          => $query . ' official audio',
    'type'       => 'video',
    'videoCategoryId' => '10', // Music category
    'maxResults' => $maxRes,
    'key'        => YOUTUBE_API_KEY,
]);

$ctx = stream_context_create(['http' => ['timeout' => 10]]);
$raw = @file_get_contents($url, false, $ctx);

if ($raw === false) {
    echo json_encode(['error' => 'Failed to reach YouTube API']);
    exit;
}

$data  = json_decode($raw, true);
$items = [];

foreach ($data['items'] ?? [] as $item) {
    $vid = $item['id']['videoId'] ?? null;
    if (!$vid) continue;
    $items[] = [
        'videoId'     => $vid,
        'title'       => $item['snippet']['title'],
        'thumbnail'   => $item['snippet']['thumbnails']['medium']['url'] ?? "https://img.youtube.com/vi/{$vid}/mqdefault.jpg",
        'channelName' => $item['snippet']['channelTitle'],
    ];
}

echo json_encode(['items' => $items]);