# Packages

Optional helper ZIP packages can be placed here during local testing, or uploaded later as GitHub Release assets.

Current preview package:

```text
swetest-php-0.1.3-preview.zip
```

SHA-256:

```text
E3938C5C9F291D1BBB90054BDA1508AB9CE3BFC9D1A2BB151A938CB6E6B193EE
```

Runtime 0.1.3-preview (2026-07-10) includes fixes from an independent audit of
the PHP port against the Swiss Ephemeris 2.10.03 C sources, verified against
the official `swetest` 2.10.03 binary. See `CHANGELOG.md` inside the package
and the "Data revision notes" section in the repository README.

Before publishing a package:

- verify that it contains no personal paths,
- verify that it contains no production secrets,
- verify that it does not bundle Swiss Ephemeris `.se1` files unless the license question is resolved,
- include clear license notes.

For the first preview repository, publishing the generated data format and generator is more important than publishing a complete bundled ephemeris distribution.
