<?php
// Self-hosted click counter for alternating WhatsApp numbers.
// No third-party service — runs entirely on your own Hostinger account.

header('Content-Type: application/json');
header('Cache-Control: no-store');

$file = __DIR__ . '/wa_counter_data.txt';

// Open (creating if needed) for read+write.
$fp = fopen($file, 'c+');
if (!$fp) {
    http_response_code(500);
    echo json_encode(['error' => 'counter_unavailable']);
    exit;
}

// Lock to avoid two simultaneous visitors reading the same number.
if (flock($fp, LOCK_EX)) {
    $current = (int) trim((string) fread($fp, 64));
    $next = $current + 1;

    ftruncate($fp, 0);
    rewind($fp);
    fwrite($fp, (string) $next);
    fflush($fp);
    flock($fp, LOCK_UN);
} else {
    fclose($fp);
    http_response_code(500);
    echo json_encode(['error' => 'lock_failed']);
    exit;
}

fclose($fp);

echo json_encode(['count' => $next]);
