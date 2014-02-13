/**
 * Class ConditionalSelect
 *
 * Provide methods to handle conditional select
 *
 * @copyright  terminal42 gmbh 2008-2010
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */
var ConditionalSelect = new Class(
{
	Binds: ['update', 'generateOptions'],
	Implements: Options,
	options: {
		includeBlankOption: false,
		blankOptionLabel: '-'
	},

	/**
	 * Initialize dynamic menu
	 */
	initialize: function(element, parent, data, values, options)
	{
		this.setOptions(options);

		this.element = $(element);
		this.parent = $(parent);
		this.data = data;
		this.values = new Hash(values);

		// Register event
		this.parent.addEvent('change', function() { this.update(this.parent) }.bind(this));

		// Register pseudo-event for ajax update
		window.addEvent('ajaxready', function() { this.update(this.parent) }.bind(this));

		// Adjust current options
		this.update();
	},

	update: function(parent)
	{
		if (parent)
		{
			this.parent = $(parent);
		}

		// Remove current options (nothing will happen if javascript is disabled)
		this.element.set('html', '');

		if (this.options.includeBlankOption)
		{
			var option = new Element('option', {
				value: ''
			});

			option.set('html', this.options.blankOptionLabel);

			this.element.appendChild(option);
		}

		// Find current selections
		var currentSelection = [];
		for( i=0; i<this.parent.options.length; i++ )
		{
			if (this.parent.options[i].selected)
			{
				currentSelection.push(this.parent.options[i].value);
			}
		}

		// Add the correct options
		for( s=0; s<currentSelection.length; s++ )
		{
			currentSelect = currentSelection[s];
			if (this.data[currentSelect])
			{
				var parentNode = this.element;
				var optGroup = false;

				if (currentSelection.length > 1)
				{
					optGroup = true;
					parentNode = new Element('optgroup', {
						label: (this.parent.getFirst(('[value='+currentSelect+']')).get('text') ? this.parent.getFirst(('[value='+currentSelect+']')).get('text') : currentSelect)
					});
				}

				// Object of arrays/options
				if (Object.prototype.toString.call(this.data[currentSelect]) !== '[object Array]')
				{
				    var groupPrefix = optGroup ? (parentNode.label + ' – ') : '';
				    optGroup = false;

    				for (k in this.data[currentSelect])
    				{
        				parentNode = new Element('optgroup', {
    						label: (groupPrefix + k)
    					});

        				parentNode = this.generateOptions(this.data[currentSelect][k], parentNode);

        				this.element.appendChild(parentNode);
    				}
				}
				else
				{
    				parentNode = this.generateOptions(this.data[currentSelect], parentNode);
    			}

				if (optGroup)
				{
					this.element.appendChild(parentNode);
				}
			}
		}

		if (this.element.options.length == 0)
		{
			this.element.options[0] = new Option('-', '');
		}

		this.element.fireEvent('change', [this.element, this.parent, this.data]);
	},

	generateOptions: function(data, parentNode)
	{
    	for (i=0; i<data.length; i++)
		{
			var option = new Element('option', {
				value: data[i]['value']
			});

			option.set('html', data[i]['label']);

			if ((!this.values && data[i]['default'] == 'true') || (this.values && data[i]['value'] && this.values.hasValue(data[i]['value'].toString())))
			{
				option.selected = true;
			}

			parentNode.appendChild(option);
		}

		return parentNode;
	}
});