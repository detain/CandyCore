<?php

declare(strict_types=1);

namespace SugarCraft\Core;

enum MouseAction: string
{
    case Press   = 'press';
    case Release = 'release';
    case Motion  = 'motion';
}
