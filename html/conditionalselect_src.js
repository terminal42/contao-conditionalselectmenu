/**
 * Class ConditionalSelect
 *
 * Provide methods to handle conditional select
 *
 * @copyright  Andreas Schempp 2008-2010
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 * @version    $Id: $
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
		this.parent.addEvent('change', function(event) { this.update(event.target) }.bind(this));
		
		// Register pseudo-event for ajax update
		window.addEvent('ajaxready', function(event) { this.update(event.target) }.bind(this));
		
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
		var count=0;
		var currentSelection = new Array();
		for( i=0; i<this.parent.options.length; i++ )
		{
			if (this.parent.options[i].selected)
			{
				currentSelection[count] = this.parent.options[i].value;
				count++;
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