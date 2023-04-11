<?php

declare(strict_types = 1);

namespace PhpAttributes\Attributes;

use Attribute;

/**
 * Action attribute class
 *
 * Extends Filter, because add_action and add_filter is actually the same:
 * https://developer.wordpress.org/reference/functions/add_action/#source
 */
#[Attribute(Attribute::TARGET_METHOD|Attribute::IS_REPEATABLE)]
class Action extends Filter
{
}
