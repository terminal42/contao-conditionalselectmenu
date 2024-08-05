<?php

declare(strict_types=1);

namespace Terminal42\ConditionalSelectMenuBundle\Widget;

use Contao\SelectMenu;
use Contao\StringUtil;

/**
 * @property string $conditionField
 */
class BackendWidget extends SelectMenu
{
    public function generate(): string
    {
        $this->arrOptions = self::prepareOptions($this->arrOptions);

        $GLOBALS['TL_JAVASCRIPT']['conditionalselect'] = 'bundles/terminal42conditionalselectmenu/conditionalselect.js';

        $strOptions = '';
        $strClass = 'tl_select';

        if ($this->multiple) {
            $this->strName .= '[]';
            $strClass = 'tl_mselect';
        }

        // Add empty option (XHTML) if there are none
        if (!\count($this->arrOptions)) {
            $this->arrOptions = [
                [
                    'value' => '',
                    'label' => $this->blankOptionLabel ?? '-',
                ],
            ];
        }

        // Make sure values is an array
        if (!\is_array($this->varValue)) {
            $this->varValue = [$this->varValue];
        }

        $this->varValue = array_map('strval', $this->varValue);

        // Get labels from parent select menu
        $arrParentOptions = [];

        if (\is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->conditionField]['reference'] ?? null)) {
            $arrParentOptions = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->conditionField]['reference'];
        } elseif (\is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->conditionField]['options'] ?? null)) {
            $arrParentOptions = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->conditionField]['options'];
        }

        foreach ($this->arrOptions as $strKey => $arrOption) {
            if (\array_key_exists('value', $arrOption)) {
                $strOptions .= \sprintf(
                    '<option value="%s"%s>%s</option>',
                    StringUtil::specialchars($arrOption['value']),
                    \in_array($arrOption['value'], $this->varValue, true) ? ' selected="selected"' : '',
                    $arrOption['label'],
                );

                continue;
            }

            $strGroup = $arrParentOptions[$strKey] ?? $strKey;
            $arrOptgroups = [];

            foreach ($arrOption as $kk => $arrOptgroup) {
                if (\array_key_exists('value', $arrOptgroup)) {
                    $arrOptgroups[] = \sprintf(
                        '<option value="%s"%s>%s</option>',
                        StringUtil::specialchars($arrOptgroup['value']),
                        \in_array($arrOptgroup['value'], $this->varValue, true) ? ' selected="selected"' : '',
                        $arrOptgroup['label'],
                    );

                    continue;
                }

                $arrSubgroups = [];

                foreach ($arrOptgroup as $arrSubgroup) {
                    $arrSubgroups[] = \sprintf(
                        '<option value="%s"%s>%s</option>',
                        StringUtil::specialchars($arrSubgroup['value']),
                        \in_array($arrSubgroup['value'], $this->varValue, true) ? ' selected="selected"' : '',
                        $arrSubgroup['label'],
                    );
                }

                if (!empty($arrSubgroups)) {
                    $strOptions .= \sprintf('<optgroup label="&nbsp;%s">%s</optgroup>', StringUtil::specialchars($strGroup.' – '.$kk), implode('', $arrSubgroups));
                }
            }

            if (!empty($arrOptgroups)) {
                $strOptions .= \sprintf('<optgroup label="&nbsp;%s">%s</optgroup>', StringUtil::specialchars($strGroup), implode('', $arrOptgroups));
            }
        }

        $strClassOptions = '';

        // Prepare Javascript
        if ($this->includeBlankOption) {
            $strClassOptions = ', {includeBlankOption: true'.($this->blankOptionLabel ? (", blankOptionLabel: '".$this->blankOptionLabel."'") : '').'}';
        }

        $strOptionsJS = "
<script>
window.addEvent('domready', function() {
  new ConditionalSelect(document.getElementById('ctrl_".$this->strId."'), document.getElementById('ctrl_".$this->conditionField."'), ".json_encode($this->arrOptions, JSON_THROW_ON_ERROR).', '.json_encode($this->varValue, JSON_THROW_ON_ERROR).$strClassOptions.');
});
</script>
';

        if ($this->chosen) {
            $strClass .= ' tl_conditionalselect_chosen';
        }

        return \sprintf(
            '<select name="%s" id="ctrl_%s" class="%s%s"%s>%s</select>',
            $this->strName,
            $this->strId,
            $strClass,
            $this->strClass ? ' '.$this->strClass : '',
            $this->getAttributes(),
            $strOptions,
        ).$strOptionsJS;
    }

    /**
     * @param array<string, array{
     *     value: string,
     *     label: string|array{
     *         value: string,
     *         label: string,
     *     }
     * }> $arrGroups
     *
     * @return array<string, array{
     *     value: string,
     *     label: string
     * }>
     */
    public static function prepareOptions(array $arrGroups): array
    {
        $arrNewOptions = [];

        foreach ($arrGroups as $group => $arrOptions) {
            foreach ($arrOptions as $k => $option) {
                if (\is_array($option) && \is_array($option['label'])) {
                    foreach ($option['label'] as $optionGroup => $arrLabels) {
                        foreach ($arrLabels as $kk => $label) {
                            $arrNewOptions[$group][$optionGroup][] = ['value' => (string) $kk, 'label' => $label];
                        }
                    }
                } else {
                    if (\is_array($option) && isset($option['value'])) {
                        $option['value'] = (string) $option['value'];
                    }

                    $arrNewOptions[$group][$k] = $option;
                }
            }
        }

        return $arrNewOptions;
    }

    protected function isValidOption($varInput): bool
    {
        if (!\is_array($varInput)) {
            $varInput = [$varInput];
        }

        // Check each option
        foreach ($varInput as $strInput) {
            if (empty($strInput)) {
                continue;
            }

            $strInput = (string) $strInput;
            $blnFound = false;

            $arrOptions = self::prepareOptions($this->arrOptions);

            foreach ($arrOptions as $v) {
                // Single dimensional array
                if (\array_key_exists('value', $v)) {
                    if ($strInput === $v['value']) {
                        $blnFound = true;
                    }
                } // Multi-dimensional array
                else {
                    foreach ($v as $vv) {
                        // Single dimensional array
                        if (\array_key_exists('value', $vv)) {
                            if ($strInput === $vv['value']) {
                                $blnFound = true;
                            }
                        } // Multi-dimensional array
                        else {
                            foreach ($vv as $vvv) {
                                if ($strInput === $vvv['value']) {
                                    $blnFound = true;
                                }
                            }
                        }
                    }
                }
            }

            if (!$blnFound) {
                return false;
            }
        }

        return true;
    }
}
