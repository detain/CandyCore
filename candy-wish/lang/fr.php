<?php

/**
 * French translations for candy-wish.
 *
 * @return array<string, string>
 */

declare(strict_types=1);

return [
    'middleware.cannot_open_stderr' => 'impossible d\'ouvrir php://stderr',
    'middleware.stderr_not_resource' => 'stderr doit être une ressource',
    'logger.cannot_open_target'      => 'impossible d\'ouvrir la cible du journal : {target}',
    'logger.invalid_target'          => 'La cible du logger doit être un chemin, une ressource ou null',
    'bubbletea.bad_factory'          => 'La fabrique BubbleTea doit retourner un objet avec une méthode run() ; reçu : {got}',
];
