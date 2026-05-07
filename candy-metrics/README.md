<img src=".assets/icon.png" alt="candy-metrics" width="160" align="right">

# CandyMetrics

<!-- BADGES:BEGIN -->
[![CI](https://github.com/detain/sugarcraft/actions/workflows/ci.yml/badge.svg?branch=master)](https://github.com/detain/sugarcraft/actions/workflows/ci.yml)
[![codecov](https://codecov.io/gh/detain/sugarcraft/branch/master/graph/badge.svg?flag=candy-metrics)](https://app.codecov.io/gh/detain/sugarcraft?flags%5B0%5D=candy-metrics)
[![Packagist Version](https://img.shields.io/packagist/v/sugarcraft/candy-metrics?label=packagist)](https://packagist.org/packages/sugarcraft/candy-metrics)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![PHP](https://img.shields.io/badge/php-%E2%89%A58.1-8892bf.svg)](https://www.php.net/)
<!-- BADGES:END -->


Lightweight telemetry primitives for SugarCraft / CandyWish servers. Counters, gauges, histograms with pluggable backends — drop-in middleware for SSH session metrics.

## Concepts

| Primitive   | Behaviour                                                       |
|-------------|------------------------------------------------------------------|
| Counter     | Monotonic value that accumulates (connect counts, errors).       |
| Gauge       | Instantaneous value that replaces on set (queue depth, RSS).     |
| Histogram   | Distribution of samples (latency, payload size).                 |

A `Registry` is the application-facing facade; it forwards every emit to the configured `Backend`. Backends decide how to persist or forward.

## Usage

```php
use SugarCraft\Metrics\Registry;
use SugarCraft\Metrics\Backend\StatsdBackend;

$reg = new Registry(new StatsdBackend('127.0.0.1', 8125));

$reg->counter('http.requests', 1, ['route' => '/api/foo', 'status' => '200']);
$reg->gauge  ('queue.depth',   42);

$stop = $reg->time('http.duration', ['route' => '/api/foo']);
handleRequest();
$stop();
```

`withTags()` returns a registry that pre-tags every emit:

```php
$req = $reg->withTags(['request_id' => $rid, 'user' => $userId]);
$req->counter('events');   // tagged with request_id + user automatically
```

## Backends

### `InMemoryBackend`

Useful for tests and for fanning out to multiple backends. Counters add up, gauges hold the last value, histograms keep every sample.

### `JsonStreamBackend`

Newline-delimited JSON, one event per line. The simplest, most diagnostic-friendly target. Default writes to `stderr`.

```jsonl
{"ts":"2026-05-02T16:30:00+00:00","kind":"counter","name":"hits","value":1,"tags":{"route":"/x"}}
```

### `StatsdBackend`

UDP datagrams in the etsy / DogStatsD wire format. Tags emitted as `|#k:v,...` (drop with `dogstatsd: false` for legacy servers).

```
hits:1|c|#route:/x,env:prod
```

### `PrometheusFileBackend`

Atomically rewrites a `.prom` textfile-collector file with the current state of every metric. Pairs with `node_exporter --collector.textfile.directory=…`. Counter values accumulate across `flush()`s; histograms expose `_count` and `_sum`.

### `MultiBackend`

Fan out to multiple backends — e.g. live StatsD plus a JSON audit trail.

```php
$reg = new Registry(new MultiBackend(
    new StatsdBackend(),
    new JsonStreamBackend('/var/log/metrics.jsonl'),
));
```

## CandyWish session middleware

Wires session telemetry into a CandyWish stack:

```php
use SugarCraft\Wish\Server;
use SugarCraft\Metrics\Registry;
use SugarCraft\Metrics\Backend\PrometheusFileBackend;
use SugarCraft\Metrics\Middleware\SessionMetrics;

$reg = new Registry(new PrometheusFileBackend('/var/lib/wish/metrics.prom'));

Server::new()
    ->use(new SessionMetrics($reg))
    ->use(/* ... your stack ... */)
    ->serve();
```

Per session this emits:

| Metric                    | Type      | Tags                              |
|---------------------------|-----------|-----------------------------------|
| `wish.session.connect`    | counter   | `user`, `term`                    |
| `wish.session.duration`   | histogram | `user`, `term`                    |
| `wish.session.error`      | counter   | `user`, `term`, `exception`       |

Pass `extraTags` (a callable receiving the `Session`) to add things like client subnet, geo, build version.

## Status

Phase 9+ — first cut. 23 tests / 57 assertions across Registry, four backends, and the SessionMetrics middleware.
