<?php

declare(strict_types=1);

namespace CandyCore\Core;

/**
 * Rich render result. Models that want per-frame control over
 * cursor shape, window title, etc. can return a {@see View} from
 * {@see Model::view()} instead of a plain string. Mirrors Bubble
 * Tea v2's `tea.View` struct.
 *
 * Only the {@see $body} field is required — everything else is
 * optional and only emitted when it changes between frames so the
 * terminal isn't spammed with redundant escapes.
 *
 * For the simple case, `Model::view()` keeps returning a string and
 * nothing changes — the runtime auto-wraps it.
 */
final class View
{
    public function __construct(
        public readonly string $body,
        public readonly ?Cursor $cursor = null,
        public readonly ?string $windowTitle = null,
    ) {}
}
