<?php

declare(strict_types = 1);

namespace PhpAttributes\Hooks;

use PhpAttributes\Attributes\Filter;

class Appereance
{
    #[Filter('body_class')]
    public function addBodyClass(array $classes): array
    {
        $classes[] = 'my-new-class';

        return $classes;
    }

    #[Filter('the_title')]
    public function changeTitle(string $title): string
    {
        $title = "{$title} got changed.";

        return $title;
    }
}
