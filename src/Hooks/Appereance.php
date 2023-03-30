<?php

declare(strict_types = 1);

namespace PhpAttributes\Hooks;

use PhpAttributes\Attributes\Filter;

/**
 * Hooked filter class
 */
class Appereance
{
    /**
     * Add a class to the body class list
     *
     * @param array $classes
     * @return array
     */
    #[Filter('body_class')]
    public function addBodyClass(array $classes): array
    {
        $classes[] = 'my-new-class';

        return $classes;
    }

    /**
     * Change the title
     *
     * @param string $title
     * @return string
     */
    #[Filter('the_title')]
    public function changeTitle(string $title): string
    {
        $title = "{$title} got changed.";

        return $title;
    }
}
