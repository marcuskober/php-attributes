<?php

declare(strict_types = 1);

namespace PhpAttributes\Main;

use PhpAttributes\Attributes\HookInterface;
use ReflectionAttribute;
use ReflectionClass;

/**
 * The main app class
 */
class App
{
    /**
     * Array of classes with hooks
     *
     * @var array
     */
    private array $hookedClasses = [];

    /**
     * Array of instanciated classes
     *
     * @var array
     */
    private array $instances = [];

    /**
     * Initialize the app
     *
     * @return void
     */
    public static function init(): void
    {
        $self = new self();
        $self->registerHooks();
    }

    /**
     * Register all the hooks in the hooked classes
     *
     * @return void
     */
    private function registerHooks(): void
    {
        $this->hookedClasses = require PHPAN_DIR . 'src/config/hookedClasses.php';

        // Loop through classes
        foreach ($this->hookedClasses as $hookedClass) {
            $reflectionHook = new ReflectionClass($hookedClass);

            // Loop through the methods of the class
            foreach ($reflectionHook->getMethods() as $method) {
                $hookAttributes = $method->getAttributes(HookInterface::class, ReflectionAttribute::IS_INSTANCEOF);

                // Loop through the attributes
                foreach ($hookAttributes as $hookAttribute) {
                    if (! isset($this->instances[$hookedClass])) {
                        $this->instances[$hookedClass] = new $hookedClass();
                    }

                    $hook = $hookAttribute->newInstance();
                    $hook->register([$this->instances[$hookedClass], $method->getName()]);
                }
            }
        }
    }
}
