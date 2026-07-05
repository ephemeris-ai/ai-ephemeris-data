<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "This script is CLI only.\n");
    exit(1);
}

function ai_ephem_fail(string $message): void
{
    fwrite(STDERR, "ERROR: " . $message . PHP_EOL);
    exit(1);
}

function ai_ephem_args(array $argv): array
{
    $args = [];
    foreach (array_slice($argv, 1) as $arg) {
        if (strpos($arg, '--') !== 0) {
            continue;
        }
        $arg = substr($arg, 2);
        $eq = strpos($arg, '=');
        if ($eq === false) {
            $args[$arg] = true;
        } else {
            $args[substr($arg, 0, $eq)] = substr($arg, $eq + 1);
        }
    }
    return $args;
}

function ai_ephem_config(): array
{
    $config = require __DIR__ . DIRECTORY_SEPARATOR . 'config.example.php';
    $local = __DIR__ . DIRECTORY_SEPARATOR . 'config.local.php';
    if (is_file($local)) {
        $override = require $local;
        if (is_array($override)) {
            $config = array_replace_recursive($config, $override);
        }
    }
    return $config;
}

function ai_ephem_bodies(): array
{
    return [
        'So' => ['swiss' => '0', 'name' => 'Sun'],
        'Mo' => ['swiss' => '1', 'name' => 'Moon'],
        'Me' => ['swiss' => '2', 'name' => 'Mercury'],
        'Ve' => ['swiss' => '3', 'name' => 'Venus'],
        'Ma' => ['swiss' => '4', 'name' => 'Mars'],
        'Ju' => ['swiss' => '5', 'name' => 'Jupiter'],
        'Sa' => ['swiss' => '6', 'name' => 'Saturn'],
        'Ur' => ['swiss' => '7', 'name' => 'Uranus'],
        'Ne' => ['swiss' => '8', 'name' => 'Neptune'],
        'Pl' => ['swiss' => '9', 'name' => 'Pluto'],
        'Nn' => ['swiss' => 'm', 'name' => 'mean North Node'],
        'Nt' => ['swiss' => 't', 'name' => 'true North Node'],
        'Ll' => ['swiss' => 'A', 'name' => 'mean Lilith / mean lunar apogee'],
        'Lt' => ['swiss' => 'B', 'name' => 'true Lilith / osculating lunar apogee'],
        'Ch' => ['swiss' => 'D', 'name' => 'Chiron'],
        'Ph' => ['swiss' => 'E', 'name' => 'Pholus'],
        'Ce' => ['swiss' => 'F', 'name' => 'Ceres'],
        'Pa' => ['swiss' => 'G', 'name' => 'Pallas'],
        'Jo' => ['swiss' => 'H', 'name' => 'Juno'],
        'Va' => ['swiss' => 'I', 'name' => 'Vesta'],
        'Wa' => ['swiss' => 'w', 'name' => 'Waldemath'],
        'Se' => ['swiss' => 'Z', 'name' => 'Selena / White Moon'],
    ];
}

function ai_ephem_body_name_to_code(): array
{
    return [
        'Sun' => 'So',
        'Moon' => 'Mo',
        'Mercury' => 'Me',
        'Venus' => 'Ve',
        'Mars' => 'Ma',
        'Jupiter' => 'Ju',
        'Saturn' => 'Sa',
        'Uranus' => 'Ur',
        'Neptune' => 'Ne',
        'Pluto' => 'Pl',
        'mean Node' => 'Nn',
        'true Node' => 'Nt',
        'mean Apogee' => 'Ll',
        'osc. Apogee' => 'Lt',
        'Chiron' => 'Ch',
        'Pholus' => 'Ph',
        'Ceres' => 'Ce',
        'Pallas' => 'Pa',
        'Juno' => 'Jo',
        'Vesta' => 'Va',
        'Waldemath' => 'Wa',
        'Selena' => 'Se',
        'White Moon' => 'Se',
        'Selena/White Moon' => 'Se',
    ];
}

function ai_ephem_sign(float $longitude): array
{
    $codes = ['Ar', 'Ta', 'Ge', 'Cn', 'Le', 'Vi', 'Li', 'Sc', 'Sg', 'Cp', 'Aq', 'Pi'];
    $lon = fmod($longitude, 360.0);
    if ($lon < 0.0) {
        $lon += 360.0;
    }
    $index = (int)floor($lon / 30.0);
    $degree = $lon - ($index * 30.0);
    return [
        'sign_index' => $index + 1,
        'sign_code' => $codes[$index],
        'degree_in_sign' => $degree,
    ];
}

function ai_ephem_round(?float $value, int $precision): ?float
{
    if ($value === null) {
        return null;
    }
    return round($value, $precision);
}

function ai_ephem_refresh_time_limit(array $config): void
{
    $seconds = max(30, (int)($config['time_limit_seconds'] ?? 300));
    if (function_exists('set_time_limit')) {
        @set_time_limit($seconds);
    }
    @ini_set('max_execution_time', (string)$seconds);
}

function ai_ephem_cadence_key(int $stepMinutes, array $config): string
{
    $configured = trim((string)($config['cadence'] ?? ''));
    if ($configured !== '') {
        if (!preg_match('/^[a-z0-9][a-z0-9_-]*$/i', $configured)) {
            ai_ephem_fail("Cadence must contain only letters, digits, underscore, or dash.");
        }
        return $configured;
    }
    return $stepMinutes . 'min';
}

function ai_ephem_sequence(array $selectedBodies, array $defs): string
{
    $sequence = '';
    foreach ($selectedBodies as $code) {
        if (!isset($defs[$code])) {
            ai_ephem_fail("Unknown body code: " . $code);
        }
        $sequence .= $defs[$code]['swiss'];
    }
    return $sequence;
}

function ai_ephem_record(DateTimeImmutable $utc, array $rows, array $selectedBodies, array $config): array
{
    $defs = ai_ephem_bodies();
    $nameMap = ai_ephem_body_name_to_code();
    $precision = (array)$config['precision'];
    $bodies = [];
    $jd = null;

    foreach ($rows as $row) {
        if (!is_array($row)) {
            continue;
        }
        $rawName = trim((string)($row['body'] ?? ''));
        $code = $nameMap[$rawName] ?? null;
        if ($code === null || !in_array($code, $selectedBodies, true)) {
            continue;
        }
        $lon = (float)($row['longitude_deg'] ?? 0.0);
        $lat = (float)($row['latitude_deg'] ?? 0.0);
        $speed = (float)($row['speed_deg_per_day'] ?? 0.0);
        $distance = array_key_exists('distance_au', $row) ? (float)$row['distance_au'] : null;
        $jd = $jd ?? (float)($row['jd_ut'] ?? 0.0);

        $sign = ai_ephem_sign($lon);
        $sign['degree_in_sign'] = ai_ephem_round((float)$sign['degree_in_sign'], (int)$precision['degree_in_sign']);

        $bodies[$code] = [
            'name' => $defs[$code]['name'],
            'longitude' => ai_ephem_round($lon, (int)$precision['longitude']),
            'latitude' => ai_ephem_round($lat, (int)$precision['latitude']),
            'distance_au' => ai_ephem_round($distance, (int)$precision['distance_au']),
            'speed_longitude' => ai_ephem_round($speed, (int)$precision['speed_longitude']),
            'retrograde' => $speed < 0.0,
            'zodiac' => $sign,
        ];
    }

    foreach ($selectedBodies as $code) {
        if (!isset($bodies[$code])) {
            ai_ephem_fail("Missing calculated body " . $code . " at " . $utc->format('c'));
        }
    }

    return [
        'schema_version' => (string)$config['schema_version'],
        'time_utc' => $utc->format('Y-m-d\TH:i:s\Z'),
        'julian_day_ut' => ai_ephem_round($jd, (int)$precision['julian_day_ut']),
        'coordinate_system' => [
            'frame' => 'geocentric',
            'zodiac' => 'tropical',
            'coordinates' => 'ecliptic',
            'longitude_unit' => 'degrees',
            'position_type' => 'apparent',
        ],
        'bodies' => $bodies,
    ];
}

function ai_ephem_open_writer(string $path, bool $gzip)
{
    $dir = dirname($path);
    if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
        ai_ephem_fail("Cannot create output directory: " . $dir);
    }
    if ($gzip) {
        $handle = gzopen($path, 'wb9');
    } else {
        $handle = fopen($path, 'wb');
    }
    if (!$handle) {
        ai_ephem_fail("Cannot open output file: " . $path);
    }
    return $handle;
}

function ai_ephem_write($handle, bool $gzip, string $line): void
{
    if ($gzip) {
        gzwrite($handle, $line);
    } else {
        fwrite($handle, $line);
    }
}

function ai_ephem_close($handle, bool $gzip): void
{
    if ($gzip) {
        gzclose($handle);
    } else {
        fclose($handle);
    }
}

function ai_ephem_generate_day(DateTimeImmutable $day, int $stepMinutes, array $config): string
{
    ai_ephem_refresh_time_limit($config);

    $swetest = trim((string)$config['swetest_php']);
    $edir = trim((string)$config['ephemeris_dir']);
    if ($swetest === '' || !is_file($swetest)) {
        ai_ephem_fail("Missing swetest_php path. Set generator/config.local.php or AI_EPHEMERIS_SWETEST_PHP.");
    }
    if ($edir === '' || !is_dir($edir)) {
        ai_ephem_fail("Missing ephemeris_dir path. Set generator/config.local.php or AI_EPHEMERIS_EPHE_DIR.");
    }

    require_once $swetest;
    ai_ephem_refresh_time_limit($config);
    if (!function_exists('swetest_calc')) {
        ai_ephem_fail("Loaded swetest.php, but swetest_calc() is not available.");
    }

    $selectedBodies = array_values((array)$config['bodies']);
    $defs = ai_ephem_bodies();
    $sequence = ai_ephem_sequence($selectedBodies, $defs);
    $gzip = (bool)$config['gzip'];
    $cadence = ai_ephem_cadence_key($stepMinutes, $config);

    $year = $day->format('Y');
    $date = $day->format('Y-m-d');
    $ext = $gzip ? '.jsonl.gz' : '.jsonl';
    $out = rtrim((string)$config['output_dir'], DIRECTORY_SEPARATOR . '/')
        . DIRECTORY_SEPARATOR . $cadence
        . DIRECTORY_SEPARATOR . $year
        . DIRECTORY_SEPARATOR . $date . $ext;

    $handle = ai_ephem_open_writer($out, $gzip);
    $cursor = $day->setTimezone(new DateTimeZone('UTC'))->setTime(0, 0, 0);
    $end = $cursor->modify('+1 day');
    $interval = new DateInterval('PT' . $stepMinutes . 'M');
    $count = 0;

    while ($cursor < $end) {
        ai_ephem_refresh_time_limit($config);
        $rows = swetest_calc([
            'date' => $cursor->format('j.n.Y'),
            'ut' => $cursor->format('H:i:s'),
            'bodies' => $sequence,
            'decimal' => true,
        ], $edir);
        $record = ai_ephem_record($cursor, $rows, $selectedBodies, $config);
        $json = json_encode($record, JSON_UNESCAPED_SLASHES);
        if (!is_string($json)) {
            ai_ephem_fail("JSON encoding failed at " . $cursor->format('c'));
        }
        ai_ephem_write($handle, $gzip, $json . "\n");
        $cursor = $cursor->add($interval);
        $count++;
    }

    ai_ephem_close($handle, $gzip);
    echo "Generated " . $out . " (" . $count . " records)\n";
    return $out;
}

$args = ai_ephem_args($argv);
$config = ai_ephem_config();

if (isset($args['help'])) {
    echo "Usage:\n";
    echo "  php generator/generate.php --date=2026-01-01 --step=60\n";
    echo "  php generator/generate.php --month=2026-01 --step=10\n";
    echo "  php generator/generate.php --year=2026 --step=60\n";
    echo "  php generator/generate.php --month=2026-01 --step=10 --cadence=10min\n";
    echo "  php generator/generate.php --year=2026 --step=10 --output-dir=../ai-ephemeris-output/data\n";
    exit(0);
}

if (isset($args['step'])) {
    $step = (int)$args['step'];
} else {
    $step = (int)$config['default_step_minutes'];
}
if ($step < 1 || $step > 1440 || 1440 % $step !== 0) {
    ai_ephem_fail("Step must be a divisor of 1440 minutes.");
}
$config['cadence'] = isset($args['cadence'])
    ? (string)$args['cadence']
    : (string)($config['default_cadence'] ?? '');

if (isset($args['output-dir'])) {
    $outputDir = trim((string)$args['output-dir']);
    if ($outputDir === '') {
        ai_ephem_fail("Invalid --output-dir, path cannot be empty.");
    }
    $config['output_dir'] = $outputDir;
}

if (isset($args['no-gzip'])) {
    $config['gzip'] = false;
}

$tz = new DateTimeZone('UTC');
if (isset($args['date'])) {
    $day = DateTimeImmutable::createFromFormat('!Y-m-d', (string)$args['date'], $tz);
    if (!$day) {
        ai_ephem_fail("Invalid --date, expected YYYY-MM-DD.");
    }
    ai_ephem_generate_day($day, $step, $config);
    exit(0);
}

if (isset($args['month'])) {
    $monthText = (string)$args['month'];
    if (!preg_match('/^\d{4}-\d{2}$/', $monthText)) {
        ai_ephem_fail("Invalid --month, expected YYYY-MM.");
    }
    $day = DateTimeImmutable::createFromFormat('!Y-m-d', $monthText . '-01', $tz);
    if (!$day) {
        ai_ephem_fail("Invalid --month, expected YYYY-MM.");
    }
    $end = $day->modify('+1 month');
    while ($day < $end) {
        ai_ephem_generate_day($day, $step, $config);
        $day = $day->modify('+1 day');
    }
    exit(0);
}

$year = isset($args['year']) ? (int)$args['year'] : (int)$config['default_year'];
if ($year < 1800 || $year > 2200) {
    ai_ephem_fail("Refusing broad generation outside 1800-2200 in this preview generator.");
}
$day = new DateTimeImmutable(sprintf('%04d-01-01T00:00:00Z', $year), $tz);
$end = $day->modify('+1 year');
while ($day < $end) {
    ai_ephem_generate_day($day, $step, $config);
    $day = $day->modify('+1 day');
}
