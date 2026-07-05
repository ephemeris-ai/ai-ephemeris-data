<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "This script is CLI only.\n");
    exit(1);
}

$file = $argv[1] ?? '';
$limit = isset($argv[2]) ? max(1, (int)$argv[2]) : 3;
if ($file === '' || !is_file($file)) {
    fwrite(STDERR, "Usage: php generator/read_example.php data/2026/2026-01-01.jsonl.gz 3\n");
    exit(1);
}

$gzip = substr($file, -3) === '.gz';
$handle = $gzip ? gzopen($file, 'rb') : fopen($file, 'rb');
if (!$handle) {
    fwrite(STDERR, "Cannot open: {$file}\n");
    exit(1);
}

$printed = 0;
while ($printed < $limit) {
    $line = $gzip ? gzgets($handle) : fgets($handle);
    if ($line === false) {
        break;
    }
    echo $line;
    $printed++;
}

if ($gzip) {
    gzclose($handle);
} else {
    fclose($handle);
}

