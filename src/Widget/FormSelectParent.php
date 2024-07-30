<?php

declare(strict_types=1);

namespace Terminal42\ConditionalSelectMenuBundle\Widget;

use Contao\FormSelect; // Contao 5
use Contao\FormSelectMenu; // Contao 4.13

if (class_exists(FormSelect::class)) {
    abstract class FormSelectParent extends FormSelect
    {
    }
} else {
    abstract class FormSelectParent extends FormSelectMenu
    {
    }
}
