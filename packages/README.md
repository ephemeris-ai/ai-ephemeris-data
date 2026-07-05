# Packages

Optional helper ZIP packages can be placed here during local testing, or uploaded later as GitHub Release assets.

Current preview package:

```text
swetest-php-0.1.2-preview.zip
```

SHA-256:

```text
CEEAA164DCF7B9E305A0354DACEA575F8C97F3D3CC5B70BABFA552FB6423D7EE
```

Before publishing a package:

- verify that it contains no personal paths,
- verify that it contains no production secrets,
- verify that it does not bundle Swiss Ephemeris `.se1` files unless the license question is resolved,
- include clear license notes.

For the first preview repository, publishing the generated data format and generator is more important than publishing a complete bundled ephemeris distribution.
