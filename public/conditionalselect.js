(function (window, document) {
    "use strict";

    window.ConditionalSelect = function (element, parent, data, values, options) {
        var selectDefaultOption = false;
        let chosen;

        function generateOptions(data, parentNode) {
            var option, i;
            selectDefaultOption = false;

            for (i = 0; i < data.length; i += 1) {
                option = document.createElement('option');
                option.value = data[i].value;
                option.innerHTML = data[i].label;

                if ((!Array.isArray(values) && data[i]['default'] === 'true') || (Array.isArray(values) && data[i].value && values.indexOf(String(data[i].value)) !== -1)) {
                    option.selected = true;
                    selectDefaultOption = true;
                }

                parentNode.appendChild(option);
            }

            return parentNode;
        }

        function update() {
            var option, i, s, k, parentNode, optGroup, currentSelect, groupPrefix, event, cssClasses,
                currentSelection = [],
                currentSelectionLabels = [];

            // Remove current options (nothing will happen if javascript is disabled)
            element.innerHTML = '';

            // Find current selections in parent (could be multi-selection)
            for (i = 0; i < parent.options.length; i += 1) {
                if (parent.options[i].selected) {
                    currentSelection.push(parent.options[i].value);
                    currentSelectionLabels.push(parent.options[i].innerHTML);
                }
            }

            // Add options for all options selected in the parent (could be multi-selection)
            for (s = 0; s < currentSelection.length; s += 1) {
                currentSelect = currentSelection[s];

                if (data[currentSelect]) {
                    parentNode = element;
                    optGroup = false;

                    if (currentSelection.length > 1) {
                        optGroup = true;
                        parentNode = document.createElement('optgroup');
                        parentNode.label = currentSelectionLabels[s] || currentSelect;
                    }

                    // Object of arrays/options
                    if (!Array.isArray(data[currentSelect])) {
                        groupPrefix = optGroup ? (parentNode.label + ' - ') : '';
                        optGroup = false;

                        for (k in data[currentSelect]) {
                            parentNode = document.createElement('optgroup');
                            parentNode.label = groupPrefix + k;
                            parentNode = generateOptions(data[currentSelect][k], parentNode);

                            element.appendChild(parentNode);
                        }
                    } else {
                        parentNode = generateOptions(data[currentSelect], parentNode);
                    }

                    if (optGroup) {
                        element.appendChild(parentNode);
                    }
                }
            }

            // Add/remove CSS class
            cssClasses = (element.className || "").split(' ');
            if (element.options.length === 0 && cssClasses.indexOf('empty') === -1) {
                cssClasses.push('empty');
                element.className = cssClasses.join(' ');
            } else if (element.options.length > 0 && cssClasses.indexOf('empty') !== -1) {
                cssClasses.splice(cssClasses.indexOf('empty'), 1);
                element.className = cssClasses.join(' ');
            }

            if (options.includeBlankOption || element.options.length === 0) {
                option = document.createElement('option');
                option.value = '';
                option.innerHTML = options.blankOptionLabel;

                if (element.options.length === 0) {
                    element.appendChild(option);
                } else {
                    element.insertBefore(option, element.children[0]);

                    if (!selectDefaultOption) {
                        element.options[0].selected = true;
                    }
                }
            }

            if (element.classList.contains('tl_conditionalselect_chosen')) {
                if (chosen) {
                    chosen.results_build();
                } else {
                    chosen = new Chosen(element);
                }
            }

            if (document.createEvent) {
                event = document.createEvent("HTMLEvents");
                event.initEvent('change', true, true);
            } else {
                event = document.createEventObject();
                event.eventType = 'change';
            }

            event.eventName = 'change';

            if (document.createEvent) {
                element.dispatchEvent(event);
            } else {
                element.fireEvent("on" + event.eventType, event);
            }
        }

        options = options || {};
        if (!options.hasOwnProperty('blankOptionLabel')) {
            options.blankOptionLabel = '-';
        }

        if (parent.addEvent) {
            parent.addEvent('change', update);
        }

        if (window.addEventListener) {
            parent.addEventListener('change', update, false);
            window.addEventListener('ajaxready', update, false);
        } else if (window.attachEvent) {
            parent.attachEvent('onchange', update);
            window.attachEvent('onajaxready', update);
        }

        update();
    };

    if (!Array.prototype.indexOf) {
        Array.prototype.indexOf = function (searchElement, fromIndex) {
            if (this === undefined || this === null) {
                throw new TypeError('"this" is null or not defined');
            }

            var length = this.length >>> 0; // Hack to convert object.length to a UInt32

            fromIndex = +fromIndex || 0;

            if (Math.abs(fromIndex) === Infinity) {
                fromIndex = 0;
            }

            if (fromIndex < 0) {
                fromIndex += length;
                if (fromIndex < 0) {
                    fromIndex = 0;
                }
            }

            for (; fromIndex < length; fromIndex += 1) {
                if (this[fromIndex] === searchElement) {
                    return fromIndex;
                }
            }

            return -1;
        };
    }

    if (!Array.isArray) {
        Array.isArray = function (vArg) {
            return Object.prototype.toString.call(vArg) === "[object Array]";
        };
    }

}(this, this.document));
