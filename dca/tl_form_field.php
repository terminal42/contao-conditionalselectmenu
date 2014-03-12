<?php

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2012 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  terminal42 gmbh 2008-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Palettes
 */
$GLOBALS['TL_DCA']['tl_form_field']['palettes']['conditionalselect'] = '{type_legend},type,name,label;{options_legend},conditionField,options;{fconfig_legend},mandatory,multiple;{expert_legend:hide},class,accesskey;{submit_legend},addSubmit';


/**
 * Fields
 */
$GLOBALS['TL_DCA']['tl_form_field']['fields']['conditionField'] = array
(
    'label'             => &$GLOBALS['TL_LANG']['tl_form_field']['conditionField'],
    'inputType'         => 'select',
    'options_callback'  => array('tl_form_field_conditionalselect', 'getConditionFields'),
    'eval'              => array('includeBlankOption'=>true, 'mandatory'=>true, 'tl_class'=>'clr'),
);


class tl_form_field_conditionalselect extends Backend
{

    /**
     * Returns an array of select-fields in the same form
     */
    public function getConditionFields($dc)
    {
        $arrReturn = array();

        $arrFields = $this->Database->prepare("SELECT * FROM tl_form_field WHERE pid=? AND id!=? AND (type=? OR type=?)")
                                    ->execute($this->Session->get('CURRENT_ID'), $dc->id, 'select', 'conditionalselect')
                                    ->fetchAllAssoc();

        foreach ($arrFields as $arrField) {
            $arrReturn[$arrField['id']] = $arrField['name'];
        }

        return $arrReturn;
    }
}

