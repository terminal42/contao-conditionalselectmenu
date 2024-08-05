<?php

declare(strict_types=1);

namespace Terminal42\ConditionalSelectMenuBundle\Widget;

use Contao\StringUtil;

/**
 * @property string $conditionField
 * @property string $classOptions
 */
class FrontendWidget extends FormSelectParent
{
    protected $strTemplate = 'form_conditionalselect';

    /**
     * Add specific attributes.
     *
     * @param string $strKey
     */
    public function __set($strKey, $varValue): void
    {
        switch ($strKey) {
            // convert array from optionwizard to treedimensional array
            case 'options':
                $this->arrOptions = StringUtil::deserialize($varValue, true);

                // If value of first option is empty, we assume it's the blank option
                if (empty($this->arrOptions[0]['value']) && !empty($this->arrOptions[0]['label'])) {
                    $this->includeBlankOption = true;

                    if (empty($this->blankOptionLabel)) {
                        $this->blankOptionLabel = $this->arrOptions[0]['label'];
                    }

                    array_shift($this->arrOptions);
                }

                if ($this->arrOptions[0]['group'] ?? false) {
                    $arrValue = [];
                    $arrOptions = [];
                    $strOptionKey = '';

                    foreach ($this->arrOptions as $arrOption) {
                        if ($arrOption['group'] ?? false) {
                            $strOptionKey = $arrOption['value'];
                        } else {
                            $arrOptions[$strOptionKey][] = $arrOption;
                        }

                        if ($arrOption['default'] ?? false) {
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
     * @param array<string, mixed> $arrAttributes
     */
    public function parse($arrAttributes = null): string
    {
        $this->arrOptions = BackendWidget::prepareOptions($this->arrOptions);

        $GLOBALS['TL_JAVASCRIPT']['conditionalselect'] = 'bundles/terminal42conditionalselectmenu/conditionalselect.js';

        $strClass = 'select';

        if ($this->multiple) {
            $this->strName .= '[]';
            $strClass = 'multiselect';
        }

        // Add empty option (XHTML) if there are none
        if (empty($this->arrOptions)) {
            $this->arrOptions = [
                [
                    'value' => '',
                    'label' => $this->blankOptionLabel ?? '-',
                ],
            ];
        }

        // Custom class
        if ('' !== $this->strClass) {
            $strClass .= ' '.$this->strClass;
        }

        $this->strClass = $strClass;

        // Prepare Javascript
        if ($this->includeBlankOption) {
            $this->classOptions = ', {includeBlankOption: true'.($this->blankOptionLabel ? (", blankOptionLabel: '".$this->blankOptionLabel."'") : '').'}';
        }

        return parent::parse($arrAttributes);
    }

    public function generate(): string
    {
        $this->arrOptions = BackendWidget::prepareOptions($this->arrOptions);

        $GLOBALS['TL_JAVASCRIPT']['conditionalselect'] = 'bundles/terminal42conditionalselectmenu/conditionalselect.js';

        $strOptions = '';
        $strClass = 'select';

        if ($this->multiple) {
            $this->strName .= '[]';
            $strClass = 'multiselect';
        }

        // Add empty option (XHTML) if there are none
        if (empty($this->arrOptions)) {
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

        array_map('strval', $this->varValue);

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

        $GLOBALS['TL_BODY'][] = "
<script>
window.addEventListener('DOMContentLoaded', function() {
  new ConditionalSelect(document.getElementById('ctrl_".$this->strId."'), document.getElementById('ctrl_".$this->conditionField."'), ".json_encode($this->arrOptions, JSON_THROW_ON_ERROR).', '.json_encode($this->varValue, JSON_THROW_ON_ERROR).$strClassOptions.');
});
</script>
';

        return \sprintf(
            '<select name="%s" id="ctrl_%s" class="%s%s"%s>%s</select>',
            $this->strName,
            $this->strId,
            $strClass,
            $this->strClass ? ' '.$this->strClass : '',
            $this->getAttributes(),
            $strOptions,
        );
    }

    /**
     * @return array<array{
     *     type: string,
     *     value?: string,
     *     label?: string,
     *     selected?: bool,
     * }>
     */
    protected function getOptions(): array
    {
        // Make sure values is an array
        if (!\is_array($this->varValue)) {
            $this->varValue = [$this->varValue];
        }

        $this->varValue = array_map('strval', $this->varValue);

        $arrParentOptions = [];

        // Get labels from parent select menu
        if (\is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->conditionField]['reference'] ?? null)) {
            $arrParentOptions = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->conditionField]['reference'];
        } elseif (\is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->conditionField]['options'] ?? null)) {
            $arrParentOptions = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->conditionField]['options'];
        }

        $arrOptions = [];
        $blnHasGroups = false;

        foreach ($this->arrOptions as $strKey => $arrOption) {
            if (\array_key_exists('value', $arrOption)) {
                $arrOptions[] = [
                    'type' => 'option',
                    'value' => StringUtil::specialchars($arrOption['value']),
                    'label' => $arrOption['label'] ?? $arrOption['value'],
                    'selected' => \in_array($arrOption['value'], $this->varValue, true),
                ];

                continue;
            }

            $strGroup = $arrParentOptions[$strKey] ?? $strKey;

            if ($blnHasGroups) {
                $arrOptions[] = ['type' => 'group_end'];
            }

            $arrOptions[] = [
                'type' => 'group_start',
                'label' => StringUtil::specialchars($strGroup),
            ];

            $blnHasGroups = true;
            $blnHasSubgroups = false;

            foreach ($arrOption as $kk => $arrOptgroup) {
                if (\array_key_exists('value', $arrOptgroup)) {
                    $arrOptions[] = [
                        'type' => 'option',
                        'value' => StringUtil::specialchars($arrOptgroup['value']),
                        'label' => $arrOptgroup['label'] ?? $arrOptgroup['value'],
                        'selected' => \in_array($arrOptgroup['value'], $this->varValue, true),
                    ];

                    continue;
                }

                if ($blnHasSubgroups) {
                    $arrOptions[] = ['type' => 'group_end'];
                }

                $arrOptions[] = [
                    'type' => 'group_start',
                    'label' => '&nbsp;'.StringUtil::specialchars($strGroup.' – '.$kk),
                ];

                $blnHasSubgroups = true;

                foreach ($arrOptgroup as $arrSubgroup) {
                    $arrOptions[] = [
                        'type' => 'option',
                        'value' => StringUtil::specialchars($arrSubgroup['value']),
                        'label' => $arrSubgroup['label'],
                        'selected' => \in_array($arrSubgroup['value'], $this->varValue, true),
                    ];
                }
            }

            if ($blnHasSubgroups) {
                $arrOptions[] = ['type' => 'group_end'];
            }
        }

        if ($blnHasGroups) {
            $arrOptions[] = ['type' => 'group_end'];
        }

        return $arrOptions;
    }

    protected function isValidOption($varInput): bool
    {
        if (!\is_array($varInput)) {
            $varInput = [$varInput];
        }

        // Check each option
        foreach ($varInput as $strInput) {
            $blnFound = false;
            $strInput = (string) $strInput;

            $arrOptions = BackendWidget::prepareOptions($this->arrOptions);

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
