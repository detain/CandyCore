<?php

declare(strict_types=1);

namespace SugarCraft\Wishlist;

/**
 * Loads a wishlist directory from disk.
 *
 * Two formats are accepted (auto-detected by extension):
 *
 *   - **JSON** (`wishlist.json`) — a top-level array of objects:
 *     ```json
 *     [
 *       { "name": "production", "host": "prod.example.com", "port": 2222, "user": "deploy" },
 *       { "name": "staging",    "host": "stage.example.com" }
 *     ]
 *     ```
 *   - **YAML-ish flat list** (`wishlist.yml`, `wishlist.yaml`) — one
 *     endpoint per `- name:` block. Parsed by a tiny built-in
 *     reader so we don't drag in `ext-yaml` for what's effectively
 *     a config file:
 *     ```yaml
 *     - name: production
 *       host: prod.example.com
 *       port: 2222
 *       user: deploy
 *     - name: staging
 *       host: stage.example.com
 *     ```
 *
 * Unknown keys are ignored so the format can grow without breaking
 * older configs.
 */
final class Config
{
    /**
     * @return list<Endpoint>
     */
    public static function load(string $path): array
    {
        if (!is_file($path)) {
            throw new \RuntimeException("wishlist config not found: {$path}");
        }
        $raw = (string) file_get_contents($path);
        return self::parse($raw, $path);
    }

    /**
     * @return list<Endpoint>
     */
    public static function parse(string $raw, string $hintPath = 'wishlist'): array
    {
        $ext = strtolower((string) pathinfo($hintPath, PATHINFO_EXTENSION));
        $rows = match ($ext) {
            'json' => self::parseJson($raw),
            'yml', 'yaml' => self::parseYaml($raw),
            default => str_starts_with(ltrim($raw), '[') || str_starts_with(ltrim($raw), '{')
                ? self::parseJson($raw)
                : self::parseYaml($raw),
        };
        return array_values(array_map(self::buildEndpoint(...), $rows));
    }

    /**
     * @return list<array<string,mixed>>
     */
    private static function parseJson(string $raw): array
    {
        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            throw new \RuntimeException('wishlist json: top-level value must be an array');
        }
        $out = [];
        foreach ($decoded as $row) {
            if (!is_array($row)) {
                throw new \RuntimeException('wishlist json: each entry must be an object');
            }
            $out[] = $row;
        }
        return $out;
    }

    /**
     * Tiny YAML-flat-list parser. Supports the subset documented
     * in the class doc — one `- key: value` block per endpoint,
     * optional 2-space-indented continuation keys, optional
     * 4-space-indented `- value` items under a value-less key
     * (used for `options:` lists), and `#` line comments.
     * Anything more sophisticated (anchors, multi-doc, nested
     * mappings) belongs in a real YAML lib; we don't need it for
     * a hosts directory.
     *
     * @return list<array<string,mixed>>
     */
    private static function parseYaml(string $raw): array
    {
        $rows = [];
        $current = null;
        // Tracks the most recent value-less key whose nested `- item`
        // lines should append into a list. Reset whenever we hit
        // anything else (a new entry header, a non-empty key:value, etc.).
        $listKey = null;
        foreach (explode("\n", $raw) as $rawLine) {
            $line = preg_replace('/\s+#.*$/', '', $rawLine) ?? $rawLine;
            if (preg_match('/^\s*(?:#.*)?$/', $line)) {
                continue;
            }
            // Nested string list under the active $listKey:
            // `    - ServerAliveInterval=30`
            if ($listKey !== null && $current !== null
                && preg_match('/^\s{2,}-\s+(\S.*?)\s*$/', $line, $m)) {
                /** @var list<mixed> $bucket */
                $bucket = is_array($current[$listKey] ?? null) ? $current[$listKey] : [];
                $bucket[] = self::yamlScalar($m[1]);
                $current[$listKey] = $bucket;
                continue;
            }
            // Any other line ends list-collection mode.
            $listKey = null;
            if (preg_match('/^-\s+(\w+):\s*(.*)$/', $line, $m)) {
                if ($current !== null) {
                    $rows[] = $current;
                }
                $current = [];
                if ($m[2] === '') {
                    $current[$m[1]] = [];
                    $listKey = $m[1];
                } else {
                    $current[$m[1]] = self::yamlScalar($m[2]);
                }
                continue;
            }
            if (preg_match('/^\s+(\w+):\s*(.*)$/', $line, $m)) {
                if ($current === null) {
                    throw new \RuntimeException("wishlist yaml: continuation before any '- name:' block");
                }
                if ($m[2] === '') {
                    $current[$m[1]] = [];
                    $listKey = $m[1];
                } else {
                    $current[$m[1]] = self::yamlScalar($m[2]);
                }
                continue;
            }
            throw new \RuntimeException("wishlist yaml: unparseable line: {$rawLine}");
        }
        if ($current !== null) {
            $rows[] = $current;
        }
        return $rows;
    }

    private static function yamlScalar(string $v): mixed
    {
        $v = trim($v);
        if ($v === '' || strtolower($v) === 'null' || $v === '~') {
            return null;
        }
        if ((str_starts_with($v, '"') && str_ends_with($v, '"'))
            || (str_starts_with($v, "'") && str_ends_with($v, "'"))) {
            return substr($v, 1, -1);
        }
        if (preg_match('/^-?\d+$/', $v)) {
            return (int) $v;
        }
        if ($v === 'true' || $v === 'yes')  return true;
        if ($v === 'false' || $v === 'no')  return false;
        return $v;
    }

    /**
     * @param array<string,mixed> $row
     */
    private static function buildEndpoint(array $row): Endpoint
    {
        if (!isset($row['name'], $row['host'])) {
            throw new \RuntimeException('wishlist entry missing required field: name or host');
        }
        $opts = [];
        if (isset($row['options']) && is_array($row['options'])) {
            foreach ($row['options'] as $o) {
                $opts[] = (string) $o;
            }
        }
        return new Endpoint(
            name:         (string) $row['name'],
            host:         (string) $row['host'],
            port:         isset($row['port']) ? (int) $row['port'] : 22,
            user:         isset($row['user']) ? (string) $row['user'] : null,
            identityFile: isset($row['identity_file']) ? (string) $row['identity_file']
                          : (isset($row['identityFile']) ? (string) $row['identityFile'] : null),
            description:  isset($row['description']) ? (string) $row['description'] : null,
            options:      $opts,
        );
    }
}
