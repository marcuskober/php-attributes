<?php

declare(strict_types = 1);

namespace PhpAttributes\config;

use PhpAttributes\Hooks\AddStupidH1;
use PhpAttributes\Hooks\Appereance;
use PhpAttributes\Hooks\Blocks;

/**
 * List of classes with hooks
 */
return [
    AddStupidH1::class,
    Appereance::class,
    Blocks::class,
];
