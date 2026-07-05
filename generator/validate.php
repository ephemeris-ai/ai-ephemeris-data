<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "This script is CLI only.\n");
    exit(1);
}

$file = $argv[1] ?? '';
if ($file === '' || !is_file($file)) {
    fwrite(STDERR, "Usage: php generator/validate.php data/2026/2026-01-01.jsonl.gz\n");
    exit(1);
}

$gzip = substr($file, -3) === '.gz';
$handle = $gzip ? gzopen($file, 'rb') : fopen($file, 'rb');
if (!$handle) {
    fwrite(STDERR, "Cannot open: {$file}\n");
    exit(1);
}

$count = 0;
$errors = 0;
$first = null;
$last = null;

while (true) {
    $line = $gzip ? gzgets($handle) : fgets($handle);
    if ($line === false) {
        break;
    }
    $line = trim($line);
    if ($line === '') {
        continue;
    }
    $count++;
    $data = json_decode($line, true);
    if (!is_array($data)) {
        $errors++;
        fwrite(STDERR, "Invalid JSON at line {$count}\n");
        continue;
    }
    if ($first === null) {
        $first = $data['time_utc'] ?? null;
    }
    $last = $data['time_utc'] ?? null;
    if (!isset($data['bodies']) || !is_array($data['bodies'])) {
        $errors++;
        fwrite(STDERR, "Missing bodies at line {$count}\n");
        continue;
    }
    foreach ($data['bodies'] as $code => $body) {
        if (!is_array($body) || !isset($body['longitude'])) {
            $errors++;
            fwrite(STDERR, "Missing longitude for {$code} at line {$count}\n");
            continue;
        }
        $lon = (float)$body['longitude'];
        if ($lon < 0.0 || $lon >= 360.0) {
            $errors++;
            fwrite(STDERR, "Longitude out of range for {$code} at line {$count}: {$lon}\n");
        }
    }
}

if ($gzip) {
    gzclose($handle);
} else {
    fclose($handle);
}

echo "File: {$file}\n";
echo "Records: {$count}\n";
echo "First: " . ($first ?? '-') . "\n";
echo "Last: " . ($last ?? '-') . "\n";
echo "Errors: {$errors}\n";
exit($errors === 0 ? 0 : 1);

