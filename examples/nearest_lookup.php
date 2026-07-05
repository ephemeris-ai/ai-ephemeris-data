<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "This script is CLI only.\n");
    exit(1);
}

$file = $argv[1] ?? __DIR__ . '/../data/2026/2026-01-01.jsonl.gz';
$requested = $argv[2] ?? '2026-01-01T12:17:00Z';
$bodyCode = $argv[3] ?? 'Mo';

if (!is_file($file)) {
    fwrite(STDERR, "File not found: {$file}\n");
    exit(1);
}

$target = new DateTimeImmutable($requested, new DateTimeZone('UTC'));
$targetTs = $target->getTimestamp();
$gzip = substr($file, -3) === '.gz';
$handle = $gzip ? gzopen($file, 'rb') : fopen($file, 'rb');
if (!$handle) {
    fwrite(STDERR, "Cannot open: {$file}\n");
    exit(1);
}

$best = null;
$bestDelta = PHP_INT_MAX;
while (true) {
    $line = $gzip ? gzgets($handle) : fgets($handle);
    if ($line === false) {
        break;
    }
    $row = json_decode(trim($line), true);
    if (!is_array($row) || !isset($row['time_utc'])) {
        continue;
    }
    $rowTs = (new DateTimeImmutable((string)$row['time_utc'], new DateTimeZone('UTC')))->getTimestamp();
    $delta = abs($rowTs - $targetTs);
    if ($delta < $bestDelta) {
        $bestDelta = $delta;
        $best = $row;
    }
}

if ($gzip) {
    gzclose($handle);
} else {
    fclose($handle);
}

if (!is_array($best)) {
    fwrite(STDERR, "No usable row found.\n");
    exit(1);
}

$body = $best['bodies'][$bodyCode] ?? null;
if (!is_array($body)) {
    fwrite(STDERR, "Body code not present in nearest row: {$bodyCode}\n");
    exit(1);
}

echo "Requested: " . $target->format('Y-m-d\TH:i:s\Z') . PHP_EOL;
echo "Nearest:   " . $best['time_utc'] . " (delta {$bestDelta}s)" . PHP_EOL;
echo "Body:      {$bodyCode} " . ($body['name'] ?? '') . PHP_EOL;
echo "Longitude: " . ($body['longitude'] ?? '') . " deg" . PHP_EOL;
echo "Sign:      " . ($body['zodiac']['sign_code'] ?? '') . " " . ($body['zodiac']['degree_in_sign'] ?? '') . PHP_EOL;

