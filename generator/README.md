# Generator

This generator creates static daily JSONL ephemeris files.

It does not call any public API. It reads a local `swetest.php` compatible runtime and local Swiss Ephemeris `.se1` files.

For a complete setup from a clean checkout, see:

```text
../GENERATE_FROM_SCRATCH.md
```

## Setup

Copy:

```text
config.example.php
```

to:

```text
config.local.php
```

and set:

```php
'swetest_php' => '/path/to/swetest.php',
'ephemeris_dir' => '/path/to/ephe',
```

Alternatively use environment variables:

```bash
AI_EPHEMERIS_SWETEST_PHP=/path/to/swetest.php
AI_EPHEMERIS_EPHE_DIR=/path/to/ephe
```

For dates around 2026 and the default body set, the minimum practical `.se1` files are:

```text
sepl_18.se1
semo_18.se1
seas_18.se1
```

## Generate one day

```bash
php generator/generate.php --date=2026-01-01 --step=10
```

## Generate one year

```bash
php generator/generate.php --year=2026 --step=60
```

Output is written under a cadence directory:

```text
data/10min/2026/2026-01-01.jsonl.gz
data/60min/2026/2026-01-01.jsonl.gz
```

For a first public release, generate a small test range first, validate it, and only then generate a full year.

## Rebuild index

```bash
php generator/build_index.php
```

## Validate

```bash
php generator/validate.php data/10min/2026/2026-01-01.jsonl.gz
```

## Read a few lines

```bash
php generator/read_example.php data/10min/2026/2026-01-01.jsonl.gz 3
```
