<?php

declare(strict_types=1);

namespace Terminal42\ConditionalSelectMenuBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class Terminal42ConditionalSelectMenuBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
