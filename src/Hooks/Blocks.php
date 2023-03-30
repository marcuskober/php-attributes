<?php

declare(strict_types = 1);

namespace PhpAttributes\Hooks;

use PhpAttributes\Attributes\Filter;

class Blocks
{
    #[Filter('allowed_block_types_all', 10, 2)]
    public function allowedBlockTypes(bool|array $allowedBlockTypes, \WP_Block_Editor_Context $blockEditorContext ): bool|array
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
