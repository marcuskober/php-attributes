<?php

declare(strict_types = 1);

namespace PhpAttributes\Hooks;

class AddStupidH1
{
    public function addH1(): void
    {
        ?>
        <h1>Stupid H1</h1>
        <?php
    }
}
