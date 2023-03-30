<?php

declare(strict_types = 1);

namespace PhpAttributes\Attributes;

use Attribute;

#[Attribute]
class Action
{
    public function __construct(
        public string $hook,
        public int $priority = 10,
        public int $acceptedArgs = 1
    )
    {
    }

    public function register(callable|array $method): void
    {
        add_action($this->hook, $method, $this->priority, $this->acceptedArgs);
    }
}
