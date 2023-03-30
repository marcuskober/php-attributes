<?php

declare(strict_types = 1);

namespace PhpAttributes\Main;

use PhpAttributes\Attributes\Action;
use ReflectionClass;

class App
{
    private array $hooks = [];
    private array $instances = [];

    public static function init(): void
    {
        $self = new self();
        $self->registerHooks();
    }

    private function registerHooks(): void
    {
        $this->hooks = require PHPAN_DIR . 'src/config/hooks.php';

        foreach ($this->hooks as $hookClass) {
            $reflectionHook = new ReflectionClass($hookClass);

            foreach ($reflectionHook->getMethods() as $method) {
                $actionAttributes = $method->getAttributes(Action::class);

                foreach ($actionAttributes as $actionAttribute) {
                    if (! isset($this->instances[$hookClass])) {
                        $this->instances[$hookClass] = new $hookClass();
                    }

                    $action = $actionAttribute->newInstance();
                    $action->register([$this->instances[$hookClass], $method->getName()]);
                }
            }
        }
    }
}
