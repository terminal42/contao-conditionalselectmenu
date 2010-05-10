<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * TYPOlight Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
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
 * @copyright  Andreas Schempp 2008-2010
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 * @version    $Id$
 */


class ConditionalSelectMenu extends SelectMenu
{

	/**
	 * Generate the widget and return it as string
	 * @return string
	 */
	public function generate()
	{
		$GLOBALS['TL_JAVASCRIPT']['conditionalselect'] = 'system/modules/conditionalselectmenu/html/conditionalselect.js';

		$strOptions = '';
		$strClass = 'tl_select';

		if ($this->multiple)
		{
			$this->strName .= '[]';
			$strClass = 'tl_mselect';
		}

		// Add empty option (XHTML) if there are none
		if (!count($this->arrOptions))
		{
			$this->arrOptions = array(array('value'=>'', 'label'=>(strlen($this->blankOptionLabel) ? $this->blankOptionLabel : '-')));
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
										 (((is_array($this->varValue) && in_array($arrOption['value'] , $this->varValue)) || $this->varValue == $arrOption['value']) ? ' selected="selected"' : ''),
										 $arrOption['label']);

				continue;
			}

			$arrOptgroups = array();

			foreach ($arrOption as $arrOptgroup)
			{			
				$arrOptgroups[] = sprintf('<option value="%s"%s>%s</option>',
										   specialchars($arrOptgroup['value']),
										   ((is_array($this->varValue) && in_array($arrOptgroup['value'] , $this->varValue) || $this->varValue == $arrOptgroup['value']) ? ' selected="selected"' : ''),
										   $arrOptgroup['label']);
			}

			$strOptions .= sprintf('<optgroup label="&nbsp;%s">%s</optgroup>', specialchars(strlen($arrParentOptions[$strKey]) ? $arrParentOptions[$strKey] : $strKey), implode('', $arrOptgroups));
		}
		
		// Prepare Javascript
		if ($this->includeBlankOption)
		{
			$strClassOptions = ", {includeBlankOption: true" . (strlen($this->blankOptionLabel) ? (", blankOptionLabel: '".$this->blankOptionLabel."'") : '') . "}";
		}
		
		$strOptionsJS = "
<script type=\"text/javascript\">
<!--//--><![CDATA[//><!--
window.addEvent('domready', function()
{
	new ConditionalSelect('ctrl_" . $this->strId . "', 'ctrl_" . $this->conditionField . "', JSON.decode('" . str_replace("'", "\'", json_encode($this->arrOptions)) . "')" . $strClassOptions . ");
});
//--><!]]>
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

