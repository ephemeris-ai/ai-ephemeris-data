# Data distribution strategy

This repository should stay small enough to download, inspect, and fork comfortably.

The default branch contains:

- documentation,
- JSON schema and AI usage notes,
- the PHP generator,
- a few small example files,
- links or manifests for larger datasets.

It should not contain full generated archives such as 1850-2050 at 10-minute cadence.

## Why

GitHub's "Download ZIP" for the default branch includes tracked files from that branch.
If full generated data is committed to `main`, a casual project download becomes a
large data download.

Large generated ranges also make normal Git operations slower and harder to maintain.

## Recommended layout

Use the repository for code and example data:

```text
main branch:
  README.md
  AI_USAGE.md
  index.json
  generator/
  schema/
  data/10min/2026/2026-01-01.jsonl.gz   example only
  data/60min/2026/2026-01-01.jsonl.gz   compact example only
```

Generate full datasets outside the repository:

```bash
php generator/generate.php --year=2026 --step=10 --output-dir=../ai-ephemeris-output/data
```

or:

```bash
AI_EPHEMERIS_OUTPUT_DIR=../ai-ephemeris-output/data php generator/generate.php --year=2026 --step=10
```

## Full archive options

For a broad public archive, use one of these channels:

1. Hugging Face Dataset repositories with monthly ZIP archives.
2. GitHub Releases with yearly or monthly archives.
3. A separate data-only repository, if the size stays reasonable.
4. Object storage or static hosting for direct daily file URLs.

The AI-facing `index.json` can then point to those external assets without making
the source repository itself huge.

The preferred public channel for generated ranges is Hugging Face. Store one
month per ZIP archive, for example:

```text
datasets/10min-2020-2030/monthly/2026/2026-07.zip
```

Inside the ZIP, keep the daily files:

```text
2026-07-01.jsonl.gz
2026-07-02.jsonl.gz
...
2026-07-31.jsonl.gz
```

This avoids thousands of small Git LFS objects and still lets AI tools download
only the month needed for a requested date.

## Suggested cadence policy

- `60min`: useful as a compact broad-range orientation layer.
- `10min`: useful for ordinary chart-position lookup.
- `2min` or finer: publish only when there is a clear use case, because size grows quickly.

For AI lookup, daily `.jsonl.gz` records remain the internal unit. For public
large-range distribution, package those daily files into monthly ZIP archives.
