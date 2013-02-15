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
	Binds: ['update'],
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

				for(i=0; i<this.data[currentSelect].length; i++)
				{
					var option = new Element('option', {
						value: this.data[currentSelect][i]['value']
					});

					option.set('html', this.data[currentSelect][i]['label']);

					if ((!this.values && this.data[currentSelect][i]['default'] == 'true') || (this.values && this.values.hasValue(this.data[currentSelect][i]['value'].toString())))
					{
						option.selected = true;
					}

					parentNode.appendChild(option);
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
	}
});