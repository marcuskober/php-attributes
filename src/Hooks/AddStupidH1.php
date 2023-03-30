<?php

declare(strict_types = 1);

namespace PhpAttributes\Hooks;

use PhpAttributes\Attributes\Action;

class AddStupidH1
{
    #[Action('the_content', 10)]
    public function addH1(string $content): void
    {
        ?>
        <h1>Stupid H1</h1>
        <?php

        echo $content;
    }
}
