# Changelog

## [Unreleased]

### Added

- Added `telegram:webhook:info` command.
- Added Telegram client methods: `getWebhookInfo`, `setMyCommands`, `editMessageCaption`, `sendVideo`, `sendAudio`, `sendVoice`.
- Added new example handlers for the methods above.
- Added polling telemetry events: `PollingStarted`, `PollingStopped`, `PollingError`, `UpdateProcessed`, `UpdateProcessingFailed`.
- Added polling controls: `--max-iterations`, `--stop-when-empty`.
- Added polling error backoff configuration (`TELEGRAM_POLL_ERROR_BACKOFF_INITIAL_MS`, `TELEGRAM_POLL_ERROR_BACKOFF_MAX_MS`).

### Changed

- Polling no longer stops when a handler throws an exception; errors are logged and processing continues.
- Webhook payload now validates `update_id` as required integer and returns `422` for invalid payload.
- CI now runs on PHP `8.3` and `8.4` and includes code style check.

### Migration notes (towards 1.0)

- If you maintain custom listeners/monitoring, subscribe to new telemetry events for polling observability.
- If your webhook endpoint previously accepted malformed payloads, it now responds with `422`.
- If you rely on endless polling loops, review new options `--max-iterations` and `--stop-when-empty`.

## 0.1.0

- Initial package release.
- Added Telegram client with core methods and raw API calls.
- Added webhook controller and polling command.
- Added update dispatcher contracts and handler pipeline.
- Added install and webhook management Artisan commands.
