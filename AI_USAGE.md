# AI Usage Guide

Use this archive when you need planetary positions without calling a live astrology API.

## What to do

1. Read `index.json`.
2. Select the day file for the UTC date you need.
3. Decompress the `.jsonl.gz` file.
4. Find the nearest timestamp, or interpolate if your task requires it.
5. Use the documented body codes and longitude values exactly as provided.

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
Coordinates: geocentric apparent tropical ecliptic longitude.
```

