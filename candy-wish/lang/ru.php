<?php

/**
 * Russian translations for candy-wish.
 *
 * @return array<string, string>
 */

declare(strict_types=1);

return [
    'middleware.cannot_open_stderr' => 'невозможно открыть php://stderr',
    'middleware.stderr_not_resource' => 'stderr должен быть ресурсом',
    'logger.cannot_open_target'      => 'невозможно открыть цель лога: {target}',
    'logger.invalid_target'          => 'Цель логгера должна быть путём, ресурсом или null',
    'bubbletea.bad_factory'          => 'Фабрика BubbleTea должна возвращать объект с методом run(); получено: {got}',
];
