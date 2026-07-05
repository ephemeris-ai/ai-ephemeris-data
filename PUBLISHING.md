# Publishing Checklist

This project is intended to be published separately from any production website.

Before publishing, run:

```bash
php -l generator/generate.php
php -l generator/build_index.php
php -l generator/validate.php
php -l generator/read_example.php
php -l examples/nearest_lookup.php
php -r '$files=["schema.json","index.json"]; foreach($files as $f){json_decode(file_get_contents($f), true); echo $f . ": " . (json_last_error()===JSON_ERROR_NONE ? "OK" : json_last_error_msg()) . PHP_EOL;}'
php generator/validate.php data/10min/2026/2026-01-01.jsonl.gz
```

Search for private or production-specific text. Use your own local list of names, emails, domains, server paths, and development paths:

```bash
rg -n "PRIVATE_NAME|PRIVATE_EMAIL|PRIVATE_DOMAIN|PRIVATE_SERVER_PATH|PRIVATE_LOCAL_PATH" .
```

The search should return no matches.

## Suggested anonymous Git identity

Use a neutral project identity, not a personal identity.

Example:

```bash
git config user.name "AI Ephemeris Data"
git config user.email "ai-ephemeris-data@users.noreply.github.com"
```

Replace the email with the private noreply email provided by the actual GitHub account.

## Suggested GitHub names

- account or organization: `ai-ephemeris-data`
- repository: `ai-ephemeris-data`

## First publish commands

Only run these after the repository content has been reviewed:

```bash
git init
git add .
git commit -m "Initial AI ephemeris data preview"
git branch -M main
git remote add origin https://github.com/ACCOUNT/ai-ephemeris-data.git
git push -u origin main
```

Alternatively, use GitHub CLI:

```bash
gh auth login
gh repo create ai-ephemeris-data --public --source=. --remote=origin --push
```

## Important publishing notes

- Do not publish `generator/config.local.php`.
- Do not publish `.se1` files unless the license question is explicitly resolved.
- Do not publish personal paths or production server paths.
- Prefer GitHub Releases for optional ZIP packages.
- For large multi-year generated data, consider a dataset host such as Hugging Face instead of putting everything directly into Git.
