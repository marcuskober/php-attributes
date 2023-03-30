<?php

declare(strict_types = 1);

namespace PhpAttributes\Main;

use PhpAttributes\Attributes\Action;
use PhpAttributes\Attributes\Filter;
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
                $actionAttributes = $method->getAttributes(Action::class);

                foreach ($actionAttributes as $actionAttribute) {
                    if (! isset($this->instances[$hookedClass])) {
                        $this->instances[$hookedClass] = new $hookedClass();
                    }

                    $action = $actionAttribute->newInstance();
                    $action->register([$this->instances[$hookedClass], $method->getName()]);
                }

                $filterAttributes = $method->getAttributes(Filter::class);

                foreach ($filterAttributes as $filterAttribute) {
                    if (! isset($this->instances[$hookedClass])) {
                        $this->instances[$hookedClass] = new $hookedClass();
                    }

                    $filter = $filterAttribute->newInstance();
                    $filter->register([$this->instances[$hookedClass], $method->getName()]);
                }
            }
        }
    }
}
