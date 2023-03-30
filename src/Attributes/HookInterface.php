<?php

declare(strict_types = 1);

namespace PhpAttributes\Attributes;

interface HookInterface
{
    public function register(callable|array $method): void;
}
