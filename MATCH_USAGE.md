# Match and Timed Event Usage

This project can be used as a neutral position lookup layer for a match, event,
election chart, or other timed situation.

It is not a prediction engine. It provides planetary positions for UTC timestamps.

## Basic workflow

1. Start with `catalog.json`.
2. Pick a dataset covering the UTC date.
3. Pick the best cadence available.
4. Follow the dataset storage rule. For monthly ZIP datasets, open
   `monthly_index.json`, download the matching month archive, and read the daily
   `.jsonl.gz` file inside it.
5. Use the scheduled start row, nearest row, or interpolation.
6. Optionally inspect a window before and after the event.

## Example

```text
match_time_local: 2026-07-06 14:00 Europe/Prague
match_time_utc:   2026-07-06T12:00:00Z
needed_file:      data/10min/2026/2026-07-06.jsonl.gz
monthly_archive:  datasets/10min-2020-2030/monthly/2026/2026-07.zip
preferred_row:    2026-07-06T12:00:00Z
```

If `2026-07-06.jsonl.gz` is not listed in the selected dataset index, the archive
does not currently contain that date/cadence.

## Cadence recommendations

- `60min`: broad daily orientation.
- `10min`: ordinary match-start and event-start lookup.
- `1min`: planned optional high-resolution layer for exact event windows.

## Event windows

For a real match or event, the scheduled start may shift. A practical AI workflow
can inspect:

```text
start - 60 minutes
start
start + 2 to 4 hours
```

The archive stores raw positions. If an AI computes aspects from those positions,
it should state its orb rules and whether it used nearest-row lookup or
interpolation.

## Planned optional files

Future datasets may add aspect-event files, for example:

```text
aspects/2026/07/2026-07-06.aspects.jsonl.gz
```

Do not assume those files exist unless they are listed in `catalog.json` or the
selected dataset index.
