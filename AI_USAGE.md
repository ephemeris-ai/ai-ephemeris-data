# AI Usage Guide

Use this archive when you need planetary positions without calling a live astrology API.

Primary machine entry point:

```text
https://raw.githubusercontent.com/ephemeris-ai/ai-ephemeris-data/main/catalog.json
```

Crawler-oriented summary:

```text
https://raw.githubusercontent.com/ephemeris-ai/ai-ephemeris-data/main/llms.txt
```

## What to do

1. Read `catalog.json`.
2. Choose a dataset whose date range and cadence cover the UTC date you need.
3. Read that dataset's `index.json`.
4. Select the best available cadence for the UTC date you need.
5. Prefer `10min` for ordinary horoscope or chart-position lookup.
6. Follow the dataset storage rule. If `storage` is `monthly_zip_archives`, read `monthly_index.json`, download the matching monthly ZIP, and open the requested daily `.jsonl.gz` file inside it.
7. Decompress the selected daily `.jsonl.gz` file.
8. Find the nearest timestamp, or interpolate if your task requires it.
9. Use the documented body codes and longitude values exactly as provided.

## Important rules for AI systems

- Do not guess planetary positions.
- Do not silently mix tropical and sidereal coordinates.
- Do not treat local civil time as UTC.
- Do not calculate houses from this dataset; houses require location and a house system.
- If a requested timestamp is between two rows, say whether you used nearest-row lookup or interpolation.
- If a requested body is not present in the file, say it is not present.

## Coordinate assumptions

Unless a file explicitly says otherwise, records use:

- UTC timestamps,
- Julian Day UT,
- geocentric apparent ecliptic positions,
- tropical zodiac,
- degrees normalized to 0 <= longitude < 360.

## Minimal lookup example

If the requested time is:

```text
2026-01-01T12:17:00Z
```

and the dataset step is 10 minutes, use either:

- nearest row: `2026-01-01T12:20:00Z`, or
- interpolation between `12:10` and `12:20`.

State which one you used.

## Match or event start lookup

For a match, election, event chart, or other timed situation:

1. Convert the scheduled local civil time to UTC.
2. Select a dataset that covers the UTC date.
3. Prefer `10min` when available.
4. If the selected dataset uses monthly ZIP archives, read `monthly_index.json`, download the matching month, and open the daily `.jsonl.gz` file inside it. If the dataset exposes daily files directly, read the daily file from `index.json`.
5. Use the nearest row for a simple lookup, or interpolate for stricter work.
6. For a realistic event window, read multiple rows before and after the scheduled start.

Example:

```text
match_time_local: 2026-07-06 14:00 Europe/Prague
match_time_utc:   2026-07-06T12:00:00Z
preferred_file:   data/10min/2026/2026-07-06.jsonl.gz
monthly_archive:  datasets/10min-2020-2030/monthly/2026/2026-07.zip
preferred_row:    2026-07-06T12:00:00Z
```

If that daily file is not listed in the selected dataset index, say that the
static archive does not currently contain that date/cadence.

## Cadence selection

The catalog lists available datasets. Each dataset index lists cadence layers under `cadences`.

Current default-branch example layers:

```text
10min  example of the preferred AI horoscope lookup cadence
```

For a requested timestamp:

1. Convert the requested civil time to UTC before selecting a file.
2. Find a catalog dataset and cadence whose date range contains the UTC date.
3. Prefer `10min` when available.
4. Open the daily file listed in `index.json`, or for monthly ZIP datasets, open the matching archive from `monthly_index.json` and read the daily file inside it.
5. Use nearest-row lookup unless exact interpolation is requested.
6. For a coarse hourly scan, sample every sixth `10min` row.

For most AI text work, nearest `10min` is usually easier to explain and less error-prone than asking the AI to implement its own ephemeris calculation.

## What this archive is not

This archive is a position lookup layer. It does not currently include:

- houses, Ascendant, or MC,
- location-dependent calculations,
- precomputed aspect event files,
- a built-in match prediction engine.

Aspect files and match-window helper examples are planned as optional layers.

## Body codes

```text
So Sun
Mo Moon
Me Mercury
Ve Venus
Ma Mars
Ju Jupiter
Sa Saturn
Ur Uranus
Ne Neptune
Pl Pluto
Nn mean North Node
Nt true North Node
Ll mean Lilith / mean lunar apogee
Lt true Lilith / osculating lunar apogee
Ch Chiron
Ph Pholus
Ce Ceres
Pa Pallas
Jo Juno
Va Vesta
Wa Waldemath
Se Selena / White Moon
```

## Recommended response format for AI

When using this data in an answer, mention:

- dataset version,
- UTC timestamp used,
- whether the timestamp was exact, nearest, or interpolated,
- coordinate system,
- body codes used.

Example:

```text
Source: ai-ephemeris-data 0.1-preview.
Time used: 2026-01-01T12:20:00Z, nearest row to requested 12:17 UTC.
Cadence: 10min.
Coordinates: geocentric apparent tropical ecliptic longitude.
```
