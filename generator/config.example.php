<?php
declare(strict_types=1);

return [
    'dataset_version' => '0.1-preview',
    'schema_version' => '1.0',

    // Local paths. Prefer overriding these in config.local.php or environment variables.
    'swetest_php' => getenv('AI_EPHEMERIS_SWETEST_PHP') ?: '',
    'ephemeris_dir' => getenv('AI_EPHEMERIS_EPHE_DIR') ?: '',

    'output_dir' => dirname(__DIR__) . DIRECTORY_SEPARATOR . 'data',
    'default_year' => 2026,
    'default_step_minutes' => 60,
    'time_limit_seconds' => 300,
    'gzip' => true,

    'bodies' => [
        'So', 'Mo', 'Me', 'Ve', 'Ma', 'Ju', 'Sa', 'Ur', 'Ne', 'Pl',
        'Nn', 'Nt', 'Ll', 'Lt', 'Ch', 'Ph', 'Ce', 'Pa', 'Jo', 'Va',
        'Wa', 'Se',
    ],

    'precision' => [
        'julian_day_ut' => 8,
        'longitude' => 8,
        'latitude' => 8,
        'distance_au' => 10,
        'speed_longitude' => 8,
        'degree_in_sign' => 8,
    ],
];
