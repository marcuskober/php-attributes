<?php

declare(strict_types = 1);

namespace PhpAttributes\Attributes;

use Attribute;

/**
 * Action attribute class
 */
#[Attribute]
class Action implements HookInterface
{
    /**
     * Construct the action class
     *
     * @param string $hook
     * @param integer $priority
     * @param integer $acceptedArgs
     */
    public function __construct(
        public string $hook,
        public int $priority = 10,
        public int $acceptedArgs = 1
    )
    {
    }

    /**
     * Register the action
     *
     * @param callable|array $method
     * @return void
     */
    public function register(callable|array $method): void
    {
        add_action($this->hook, $method, $this->priority, $this->acceptedArgs);
    }
}
