<?php

include __DIR__ . '/../config/db.php';

$result = mysqli_query(
    $conn,
    "SELECT qr_code FROM payment_settings WHERE is_active = 1 ORDER BY id DESC LIMIT 1"
);

if (!$result) {
    http_response_code(500);
    exit;
}

$row = mysqli_fetch_assoc($result);

if (!$row || empty($row['qr_code'])) {
    http_response_code(404);
    exit;
}

$finfo = new finfo(FILEINFO_MIME_TYPE);
$mimeType = $finfo->buffer($row['qr_code']);

$allowedTypes = [
    'image/png',
    'image/jpeg',
    'image/webp'
];

if (!in_array($mimeType, $allowedTypes, true)) {
    http_response_code(415);
    exit;
}

header('Content-Type: ' . $mimeType);
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

echo $row['qr_code'];
exit;
