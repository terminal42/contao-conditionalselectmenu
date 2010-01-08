<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * TYPOlight webCMS
 * Copyright (C) 2005 Leo Feyer
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at http://www.gnu.org/licenses/.
 *
 * PHP version 5
 * @copyright  Andreas Schempp 2009
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


class FormConditionalSelectMenu extends FormSelectMenu
{
	/**
	 * Add specific attributes
	 * @param string
	 * @param mixed
	 */
	public function __set($strKey, $varValue)
	{
		switch ($strKey)
		{
			case 'options':
				$this->arrOptions = deserialize($varValue);
				// convert array from optionwizard to treedimensional array
				if ($this->arrOptions[0]['group'])
				{
					$arrValue = array();
					$arrOptions = array();
					foreach( $this->arrOptions as $arrOption )
					{
						if ($arrOption['group'])
						{
							$strOptionKey = $arrOption['value'];
						}
						else
						{
							$arrOptions[$strOptionKey][] = $arrOption;
						}
						
						if ($arrOption['default'])
						{
							$arrValue[] = $arrOption['value'];
						}
					}
					
					$this->varValue = $arrValue;
					$this->arrOptions = $arrOptions;
				}
				break;

			default:
				parent::__set($strKey, $varValue);
				break;
		}
	}
	
	
	/**
	 * Generate the widget and return it as string
	 * @return string
	 */
	public function generate()
	{
		$GLOBALS['TL_JAVASCRIPT']['conditionalselect'] = 'system/modules/conditionalselectmenu/html/conditionalselect.js';
		
		$strOptions = '';
		$strOptionsJS = '<script type="text/javascript">var ctrl_'.$this->strId.'_options = new Array(); ';
		$strClass = 'select';

		if ($this->multiple)
		{
			$this->strName .= '[]';
			$strClass = 'multiselect';
		}

		// Add empty option (XHTML) if there are none
		if (!count($this->arrOptions))
		{
			$this->arrOptions = array(array('value'=>'', 'label'=>(strlen($this->blankOptionLabel) ? $this->blankOptionLabel : '-')));
		}

		foreach ($this->arrOptions as $strKey=>$arrOption)
		{
			if (array_key_exists('value', $arrOption))
			{
				$strOptions .= sprintf('<option value="%s"%s>%s</option>',
										 specialchars($arrOption['value']),
										 (((is_array($this->varValue) && in_array($arrOption['value'] , $this->varValue)) || $this->varValue == $arrOption['value']) ? ' selected="selected"' : ''),
										 $arrOption['label']);

				continue;
			}

			$arrOptgroups = array();
			
			$strOptionsJS .= sprintf("ctrl_%s_options['%s'] = new Array();\n", $this->strId, specialchars($strKey));

			foreach ($arrOption as $arrOptgroup)
			{
				$strOptionsJS .= sprintf("keys = new Array('value', 'label'%s);\nvar values = new Array('%s', '%s'%s);\nctrl_%s_options['%s'].include(values.associate(keys));\n",
									((is_array($this->varValue) && in_array($arrOptgroup['value'] , $this->varValue)) || $this->varValue == $arrOptgroup['value']) ? ", 'default'" : '',
									$arrOptgroup['value'],
									$arrOptgroup['label'],
									((is_array($this->varValue) && in_array($arrOptgroup['value'] , $this->varValue)) || $this->varValue == $arrOptgroup['value']) ? ", 'true'" : '',
									$this->strId,
									specialchars($strKey));
			
				$arrOptgroups[] = sprintf('<option value="%s"%s>%s</option>',
										   specialchars($arrOptgroup['value']),
										   ((is_array($this->varValue) && in_array($arrOptgroup['value'] , $this->varValue) || $this->varValue == $arrOptgroup['value']) ? ' selected="selected"' : ''),
										   $arrOptgroup['label']);
			}

			$strOptions .= sprintf('<optgroup label="&nbsp;%s">%s</optgroup>', specialchars($strKey), implode('', $arrOptgroups));
		}
		
		$strOptionsJS .= sprintf("window.addEvent('domready', function() { ConditionalSelect.init('ctrl_%s', 'ctrl_%s', ctrl_%s_options, ". ($this->includeBlankOption ? 'true' : 'false') .", '" . (strlen($this->blankOptionLabel) ? $this->blankOptionLabel : '-') . "') } );</script>", $this->strId, $this->conditionField, $this->strId);

		return sprintf('<select name="%s" id="ctrl_%s" class="%s%s"%s>%s</select>',
						$this->strName,
						$this->strId,
						$strClass,
						(strlen($this->strClass) ? ' ' . $this->strClass : ''),
						$this->getAttributes(),
						$strOptions) . $strOptionsJS . $this->addSubmit();
	}
}

