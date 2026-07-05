# Generator

This generator creates static daily JSONL ephemeris files.

It does not call any public API. It reads a local `swetest.php` compatible runtime and local Swiss Ephemeris `.se1` files.

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

## Generate one day

```bash
php generator/generate.php --date=2026-01-01 --step=60
```

## Generate one year

```bash
php generator/generate.php --year=2026 --step=10
```

For a first public release, generate a small test range first, validate it, and only then generate a full year.

## Validate

```bash
php generator/validate.php data/2026/2026-01-01.jsonl.gz
```

## Read a few lines

```bash
php generator/read_example.php data/2026/2026-01-01.jsonl.gz 3
```

