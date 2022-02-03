<?php

use Terminal42\ConditionalSelectMenuBundle\Widget\BackendWidget;
use Terminal42\ConditionalSelectMenuBundle\Widget\FrontendWidget;

$GLOBALS['BE_FFL']['conditionalselect'] = BackendWidget::class;
$GLOBALS['TL_FFL']['conditionalselect'] = FrontendWidget::class;
