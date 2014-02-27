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
					foreach ($this->arrOptions as $arrOption)
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
    	$this->arrOptions = ConditionalSelectMenu::prepareOptions($this->arrOptions);

		$GLOBALS['TL_JAVASCRIPT']['conditionalselect'] = 'system/modules/conditionalselectmenu/html/conditionalselect' . ($GLOBALS['TL_CONFIG']['debugMode'] ? '' : '.min') . '.js';

		$strOptions = '';
		$strClass = 'select';

		if ($this->multiple)
		{
			$this->strName .= '[]';
			$strClass = 'multiselect';
		}

		// Add empty option (XHTML) if there are none
		if (empty($this->arrOptions))
		{
			$this->arrOptions = array(array('value'=>'', 'label'=>(strlen($this->blankOptionLabel) ? $this->blankOptionLabel : '-')));
		}

		// Make sure values is an array
		if (!is_array($this->varValue))
		{
			$this->varValue = array($this->varValue);
		}

		// Get labels from parent select menu
		$arrParentOptions = array();
		if (is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->conditionField]['reference']))
		{
			$arrParentOptions = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->conditionField]['reference'];
		}
		elseif (is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->conditionField]['options']))
		{
			$arrParentOptions = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->conditionField]['options'];
		}

		foreach ($this->arrOptions as $strKey=>$arrOption)
		{
			if (array_key_exists('value', $arrOption))
			{
				$strOptions .= sprintf('<option value="%s"%s>%s</option>',
										 specialchars($arrOption['value']),
										 (in_array($arrOption['value'] , $this->varValue) ? ' selected="selected"' : ''),
										 $arrOption['label']);

				continue;
			}

			$strGroup = strlen($arrParentOptions[$strKey]) ? $arrParentOptions[$strKey] : $strKey;
			$arrOptgroups = array();

			foreach ($arrOption as $kk => $arrOptgroup)
			{
    			if (array_key_exists('value', $arrOptgroup))
    			{
    				$arrOptgroups[] = sprintf('<option value="%s"%s>%s</option>',
    										   specialchars($arrOptgroup['value']),
    										   (in_array($arrOptgroup['value'] , $this->varValue) ? ' selected="selected"' : ''),
    										   $arrOptgroup['label']);

    				continue;
    			}

    			$arrSubgroups = array();

    			foreach ($arrOptgroup as $arrSubgroup)
    			{
        			$arrSubgroups[] = sprintf('<option value="%s"%s>%s</option>',
    										   specialchars($arrSubgroup['value']),
    										   (in_array($arrSubgroup['value'] , $this->varValue) ? ' selected="selected"' : ''),
    										   $arrSubgroup['label']);
    			}

    			if (!empty($arrSubgroups))
    			{
        			$strOptions .= sprintf('<optgroup label="&nbsp;%s">%s</optgroup>', specialchars($strGroup . ' – ' . $kk), implode('', $arrSubgroups));
        		}
			}

			if (!empty($arrOptgroups))
			{
    			$strOptions .= sprintf('<optgroup label="&nbsp;%s">%s</optgroup>', specialchars($strGroup), implode('', $arrOptgroups));
    		}
		}

		// Prepare Javascript
		if ($this->includeBlankOption)
		{
			$strClassOptions = ", {includeBlankOption: true" . (strlen($this->blankOptionLabel) ? (", blankOptionLabel: '".$this->blankOptionLabel."'") : '-') . "}";
		}

		$strOptionsJS = "
<script>
new ConditionalSelect(document.getElementById('ctrl_" . $this->strId . "'), document.getElementById('ctrl_" . $this->conditionField . "'), " . json_encode($this->arrOptions) . ", " . json_encode($this->varValue) . $strClassOptions . ");
</script>
";

		return sprintf('<select name="%s" id="ctrl_%s" class="%s%s"%s>%s</select>',
						$this->strName,
						$this->strId,
						$strClass,
						(strlen($this->strClass) ? ' ' . $this->strClass : ''),
						$this->getAttributes(),
						$strOptions) . $strOptionsJS . $this->addSubmit();
	}
}

