# Generate Data From Scratch

This guide is for humans, scripts, and AI agents that want to generate the static JSONL ephemeris files locally.

The repository does not call a live API. Generation is done locally from:

1. the generator in `generator/`,
2. the bundled preview runtime package in `packages/`,
3. local Swiss Ephemeris `.se1` files.

## 1. Requirements

- PHP 7.4 or newer
- local disk space for ephemeris files and generated JSONL/GZIP output
- Swiss Ephemeris compatible `.se1` files

## 2. Unpack the preview PHP runtime

From the repository root:

```bash
mkdir runtime
unzip packages/swetest-php-0.1.3-preview.zip -d runtime
```

The runtime path used by the generator will then be:

```text
runtime/swetest-php-0.1.3-preview/src/swetest.php
```

## 3. Download the required `.se1` files

For the current preview body set and dates around 2026, the minimal practical files are:

```text
sepl_18.se1
semo_18.se1
seas_18.se1
```

Put them into:

```text
ephe/
```

The public Swiss Ephemeris GitHub repository contains an `ephe` folder:

```text
https://github.com/aloistr/swisseph/tree/master/ephe
```

Astrodienst also documents Swiss Ephemeris code and data downloads here:

```text
https://www.astro.com/swisseph/swedownload_e.htm
https://www.astro.com/ftp/swisseph/ephe/
```

Important: check and follow the Swiss Ephemeris license requirements before redistributing source files, binaries, `.se1` files, or generated data in a public service.

## 4. Create local generator config

Copy:

```text
generator/config.example.php
```

to:

```text
generator/config.local.php
```

Example local config:

```php
<?php
declare(strict_types=1);

return [
    'swetest_php' => dirname(__DIR__) . '/runtime/swetest-php-0.1.3-preview/src/swetest.php',
    'ephemeris_dir' => dirname(__DIR__) . '/ephe',
];
```

`generator/config.local.php`, `runtime/`, and `ephe/` are ignored by Git.

## 5. Generate one test day

```bash
php generator/generate.php --date=2026-01-01 --step=10
```

Validate it:

```bash
php generator/validate.php data/10min/2026/2026-01-01.jsonl.gz
```

Read one line:

```bash
php generator/read_example.php data/10min/2026/2026-01-01.jsonl.gz 1
```

## 6. Generate a larger range

For a month or year, start with a small `10min` range first:

```bash
php generator/generate.php --month=2026-01 --step=10
```

This writes files under `data/10min/`.

After that validates cleanly, expand the same cadence to a year:

```bash
php generator/generate.php --year=2026 --step=10
```

For broad fine-cadence ranges, write outside this repository so the default
branch remains small:

```bash
php generator/generate.php --year=2026 --step=10 --output-dir=../ai-ephemeris-output/data
```

After generation, rebuild the dataset index:

```bash
php generator/build_index.php
```

Only after validation should you generate broader or finer cadences such as 5, 2, or 1 minute.

Reproducibility note: files regenerated with runtime 0.1.3-preview or newer
are not byte-identical to the ranges published before 2026-07-10. Longitudes,
latitudes, and speeds may differ in the 8th decimal of a degree (<= 0.0005
arcsec), and `distance_au` of `Nn`, `Nt`, `Ll`, and `Lt` now contains real
values instead of `0.0` / constant `0.002712`. See "Data revision notes" in
the repository README.

## 7. Notes for AI agents

- Do not call a public live API for bulk generation.
- Do not guess missing positions.
- Download `.se1` files only from a source whose license and provenance you can verify.
- Keep all generated records in UTC.
- Validate every generated file before using it.
