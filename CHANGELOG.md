# Changelog

## Unreleased

- Allow dynamic UUID properties on PHP 8.2 and later without deprecation notices, preserving subclass storage and caller-added properties.
- Handle null and unsupported input types without PHP warnings or type errors during validation and import.
- Preserve the version 1 default, accepted import formats, public properties, serialization, namespace, alias, and PHP 7.0 minimum.
- Keep the legacy invalid-import and invalid-comparison results.
- Replace Travis CI with GitHub Actions coverage for PHP 7.0 through 8.5 and Laravel 5.3 through 13.
- Add regression tests, Laravel integration tests, code style checks, and dependency auditing.
- Remove the unused Faker development dependency and allow PHPUnit versions appropriate for each PHP release.
- Refresh documentation, light and dark README banners, badges, and the license year.

## 2.x

- Use uppercase constant names, including `NS_DNS` in place of `nsDNS`.
- Adopt PSR-2 formatting.
