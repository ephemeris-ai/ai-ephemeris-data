<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "This script is CLI only.\n");
    exit(1);
}

$root = dirname(__DIR__);
$indexPath = $root . DIRECTORY_SEPARATOR . 'index.json';
$dataRoot = $root . DIRECTORY_SEPARATOR . 'data';

if (!is_file($indexPath)) {
    fwrite(STDERR, "Missing index.json\n");
    exit(1);
}
if (!is_dir($dataRoot)) {
    fwrite(STDERR, "Missing data directory\n");
    exit(1);
}

$index = json_decode((string)file_get_contents($indexPath), true);
if (!is_array($index)) {
    fwrite(STDERR, "Cannot parse index.json\n");
    exit(1);
}

function ai_ephem_index_step_from_cadence(string $cadence): ?int
{
    if (preg_match('/^(\d+)min$/', $cadence, $m)) {
        return (int)$m[1];
    }
    return null;
}

function ai_ephem_index_recommended_use(string $cadence, ?int $step): string
{
    if ($step === 1) {
        return 'high-resolution match-window and exact-time lookup layer';
    }
    if ($step === 10) {
        return 'recommended for AI horoscope lookup and ordinary chart-position use';
    }
    if ($step === 60) {
        return 'compact orientation layer for broad lookup and coarse scans';
    }
    return 'special cadence layer';
}

function ai_ephem_index_source_metadata(): array
{
    return [
        'ephemeris_source' => 'Swiss Ephemeris compatible calculations',
        'runtime' => 'swetest.php preview runtime',
        'zodiac' => 'tropical',
        'ayanamsa' => null,
        'frame' => 'geocentric',
        'coordinates' => 'ecliptic',
        'position_type' => 'apparent',
        'time_scale' => 'UTC input, Julian Day UT output',
        'node_codes' => [
            'Nn' => 'mean North Node',
            'Nt' => 'true North Node',
        ],
        'lilith_codes' => [
            'Ll' => 'mean lunar apogee / mean Lilith',
            'Lt' => 'osculating lunar apogee / true Lilith',
        ],
        'special_points' => [
            'Wa' => 'Waldemath',
            'Se' => 'Selena / White Moon',
        ],
    ];
}

$files = [];
$cadences = [];
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($dataRoot, FilesystemIterator::SKIP_DOTS)
);

foreach ($iterator as $file) {
    if (!$file instanceof SplFileInfo || !$file->isFile()) {
        continue;
    }
    $relative = str_replace('\\', '/', substr($file->getPathname(), strlen($root) + 1));
    if (!preg_match('~^data/([^/]+)/(\d{4})/(\d{4}-\d{2}-\d{2})\.jsonl\.gz$~', $relative, $m)) {
        continue;
    }
    $cadence = $m[1];
    $year = (int)$m[2];
    $date = $m[3];
    $step = ai_ephem_index_step_from_cadence($cadence);
    $records = $step !== null && $step > 0 && 1440 % $step === 0 ? (int)(1440 / $step) : null;

    $files[] = [
        'cadence' => $cadence,
        'year' => $year,
        'date' => $date,
        'path' => $relative,
        'compression' => 'gzip',
        'records' => $records,
        'step_minutes' => $step,
    ];

    if (!isset($cadences[$cadence])) {
        $cadences[$cadence] = [
            'cadence' => $cadence,
            'step_minutes' => $step,
            'records_per_day' => $records,
            'date_range' => [
                'start' => $date,
                'end' => $date,
            ],
            'file_count' => 0,
            'recommended_use' => ai_ephem_index_recommended_use($cadence, $step),
        ];
    }
    $cadences[$cadence]['file_count']++;
    if (strcmp($date, (string)$cadences[$cadence]['date_range']['start']) < 0) {
        $cadences[$cadence]['date_range']['start'] = $date;
    }
    if (strcmp($date, (string)$cadences[$cadence]['date_range']['end']) > 0) {
        $cadences[$cadence]['date_range']['end'] = $date;
    }
}

usort($files, static function (array $a, array $b): int {
    $cadenceCompare = strcmp((string)$a['cadence'], (string)$b['cadence']);
    if ($cadenceCompare !== 0) {
        return $cadenceCompare;
    }
    return strcmp((string)$a['date'], (string)$b['date']);
});
ksort($cadences);

$dateStart = null;
$dateEnd = null;
foreach ($files as $entry) {
    $date = (string)$entry['date'];
    if ($dateStart === null || strcmp($date, $dateStart) < 0) {
        $dateStart = $date;
    }
    if ($dateEnd === null || strcmp($date, $dateEnd) > 0) {
        $dateEnd = $date;
    }
}

$index['status'] = 'preview';
$index['source'] = ai_ephem_index_source_metadata();
$index['date_range'] = [
    'start' => $dateStart,
    'end' => $dateEnd,
];
$index['cadences'] = array_values($cadences);
$index['file_count'] = count($files);
$index['files'] = $files;
unset($index['cadence']);

$json = json_encode($index, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
if (!is_string($json)) {
    fwrite(STDERR, "Cannot encode index.json\n");
    exit(1);
}
file_put_contents($indexPath, $json . "\n");

echo "index_files=" . count($files)
    . " cadences=" . count($cadences)
    . " start=" . ($dateStart ?? '')
    . " end=" . ($dateEnd ?? '')
    . "\n";
