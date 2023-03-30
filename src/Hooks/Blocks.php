<?php

declare(strict_types = 1);

namespace PhpAttributes\Hooks;

use PhpAttributes\Attributes\Filter;
use WP_Block_Editor_Context;

/**
 * Hooked class for allowing certain blocks only
 */
class Blocks
{
    /**
     * Allow certain blocks only
     *
     * @param boolean|array $allowedBlockTypes
     * @param \WP_Block_Editor_Context $blockEditorContext
     * @return boolean|array
     */
    #[Filter('allowed_block_types_all', 10, 2)]
    public function allowedBlockTypes(bool|array $allowedBlockTypes, WP_Block_Editor_Context $blockEditorContext ): bool|array
    {
        if (empty($blockEditorContext->post)) {
            return $allowedBlockTypes;
        }

        return [
			'core/paragraph',
			'core/heading',
			'core/list',
        ];
    }
}
