# AI Ephemeris Data

AI-ready static ephemeris data for astrological and astronomical experiments.

The goal of this project is simple:

- provide precomputed planetary positions in a format that AI tools can read reliably,
- avoid repeated live API calls for bulk or automated use,
- keep the data deterministic, documented, and easy to validate.

This repository is intentionally independent from any production website. It does not provide a live calculation API. Consumers should read the static data files directly.

## Dataset model

The archive is designed as daily JSON Lines files:

```text
data/
  2026/
    2026-01-01.jsonl.gz
    2026-01-02.jsonl.gz
```

Each line contains one UTC timestamp and all configured bodies for that timestamp.

The current local preview contains one generated sample day:

```text
data/2026/2026-01-01.jsonl.gz
```

It uses a 60-minute step and is meant for format validation, not as the final public cadence.

The default coordinate model is:

- time scale: UTC input, Julian Day UT in output
- frame: geocentric
- zodiac: tropical
- coordinates: ecliptic longitude and latitude
- longitude unit: degrees, normalized to 0 <= longitude < 360
- no houses, no Ascendant, no MC, no birth-place dependent data

## First supported body set

The planned public body codes are:

```text
So Mo Me Ve Ma Ju Sa Ur Ne Pl Nn Nt Ll Lt Ch Ph Ce Pa Jo Va Wa Se
```

Where:

- `Nn` = mean North Node
- `Nt` = true North Node
- `Ll` = mean lunar apogee / mean Lilith
- `Lt` = osculating lunar apogee / true Lilith
- `Wa` = Waldemath
- `Se` = Selena / White Moon

## Generator

The generator is in `generator/`.

It expects:

- PHP 7.4 or newer,
- a compatible `swetest.php` pure-PHP runtime,
- local Swiss Ephemeris `.se1` files.

The `.se1` files are not included in this repository.

Example:

```bash
php generator/generate.php --date=2026-01-01 --step=60
php generator/validate.php data/2026/2026-01-01.jsonl.gz
```

For local configuration, copy:

```text
generator/config.example.php
```

to:

```text
generator/config.local.php
```

and set local paths there. The local config file is ignored by Git.

## AI usage

See [AI_USAGE.md](AI_USAGE.md).

For a small PHP lookup example, see [examples/nearest_lookup.php](examples/nearest_lookup.php).

## License and source data

This is a preview package. The generator can work with Swiss Ephemeris compatible `.se1` files, but those files are not redistributed here.

Before publishing a larger public archive, verify the licensing terms for the calculation engine, generated data, and ephemeris source files.

See [LICENSE-NOTICE.md](LICENSE-NOTICE.md).
