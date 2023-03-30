<?php

declare(strict_types = 1);

namespace PhpAttributes\Main;

use PhpAttributes\Attributes\HookInterface;
use ReflectionAttribute;
use ReflectionClass;

class App
{
    private array $hookedClasses = [];
    private array $instances = [];

    public static function init(): void
    {
        $self = new self();
        $self->registerHooks();
    }

    private function registerHooks(): void
    {
        $this->hookedClasses = require PHPAN_DIR . 'src/config/hookedClasses.php';

        foreach ($this->hookedClasses as $hookedClass) {
            $reflectionHook = new ReflectionClass($hookedClass);

            foreach ($reflectionHook->getMethods() as $method) {
                $hookAttributes = $method->getAttributes(HookInterface::class, ReflectionAttribute::IS_INSTANCEOF);

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
