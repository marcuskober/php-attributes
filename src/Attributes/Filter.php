<?php

declare(strict_types = 1);

namespace PhpAttributes\Attributes;

use Attribute;

/**
 * Fitler attribute class
 */
#[Attribute(Attribute::TARGET_METHOD|Attribute::IS_REPEATABLE)]
class Filter implements HookInterface
{
    /**
     * Construct the fitler class
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
     * Register the filter
     *
     * @param callable|array $method
     * @return void
     */
    public function register(callable|array $method): void
    {
        add_filter($this->hook, $method, $this->priority, $this->acceptedArgs);
    }
}
