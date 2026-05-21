# Changelog

All notable changes to FormFlow Lite are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [3.2.6] - 2026-05-21

### Changed

- **Single "FormFlow" admin menu.** Removed the duplicate React SPA top-level menu entry — its deep-link route (`formflow-editor`) was never registered with WordPress's admin router, causing a "Sorry, you are not allowed to access this page" `wp_die()` on any direct load. The remaining menu (previously "FF Forms (Legacy)") is renamed to just "FormFlow". The React handler is left in `class-admin.php` for a future re-introduction once the routing is fixed.
- **Safety-net redirects.** Old `formflow-lite-app` and `formflow-editor` URLs now redirect to the dashboard instead of `wp_die`-ing.

## [3.2.5] - 2026-05-21

### Fixed (P0 — correctness, enrollment-critical)

- **Idempotency guard on early enrollment.** `fffl_enroll_early` now short-circuits when the session already has `enrollment_completed=true` and returns the cached FSR#/caNo. A retried AJAX submit (slow network, double-click, browser back+resubmit) used to fire a second live IntelliSource enrollment for the same customer; that double-enrollment path is closed.
- **Booking success classification.** `parse_booking_response` now requires an explicit positive marker (`confirmation`, `caNo`, or `fsr`) and treats `<error_cd>`, "No available slots", and HTML error pages as failures. Previously the success check was `!str_contains('error')`, which false-positived on any IS response that didn't literally contain the word "error".
- **Region-scoped schedule slot cache key.** `CacheManager::cache_schedule_slots()` / `get_schedule_slots()` now require account number + ZIP, so one customer's IS slots cannot be served from cache to another customer in a different service region. (Latent today — the cache is not wired into the hot AJAX path — but fixed before it can be.)
- **Missing IntelliSource XML parser class.** The connector and loader referenced `IntelliSourceXmlParser`, but the source file was absent from the repo (would fatal on plugin activation). Switched to `\FFFL\Api\XmlParser` and removed the dead `require_once`.

### Notes

- Verified by `tests/smoke-3.2.5.php` (run with `php tests/smoke-3.2.5.php`). 13/13 assertions green.

## [3.2.4] - 2026-05-15

### Fixed

- Fix fatal — move ABSPATH guard after namespace declaration in the namespaced files (cd6f378-style regression that 500'd sites). Durable release; prod-class hotfix already applied 2026-05-15.

## [3.2.3]

### Security

- Fix unprepared SQL queries in deactivator and diagnostics
- Add rate limiting to public embed endpoints (submit 10/min, validate 20/min, schedule 20/min)
