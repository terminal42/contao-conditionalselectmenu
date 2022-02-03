<?php

/**
 * Palettes
 */
$GLOBALS['TL_DCA']['tl_form_field']['palettes']['conditionalselect'] = '{type_legend},type,name,label;{options_legend},conditionField,options;{fconfig_legend},mandatory,multiple;{expert_legend:hide},class,accesskey;{submit_legend},addSubmit';

/**
 * Fields
 */
$GLOBALS['TL_DCA']['tl_form_field']['fields']['conditionField'] = [
    'exclude' => true,
    'inputType' => 'select',
    'eval' => ['includeBlankOption' => true, 'mandatory' => true, 'tl_class' => 'clr'],
    'sql' => "int(10) NOT NULL default '0'",
];
