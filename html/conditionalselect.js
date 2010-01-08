/**
 * @copyright  Andreas Schempp 2009
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Class ConditionalSelect
 *
 * Provide methods to handle conditional select
 * @copyright  Andreas Schempp 2008
 * @author     Andreas Schempp <andreas@schempp.ch
 */
var ConditionalSelect =
{
	/**
	 * Initialize dynamic menu
	 */	
	init: function(el, id, options, includeBlank, blankLabel)
	{
		// Adjust current options
		ConditionalSelect.update(el, id, options, includeBlank, blankLabel);
		
		// Register event
		$(id).addEvent('change', function(){
		    ConditionalSelect.update(el, id, options, includeBlank, blankLabel);
		});
	},
	
	update: function(el, id, options, includeBlank, blankLabel)
	{
		// Remove current options (nothing will happen if javascript is disabled)
		if ($(el).setHTML)
		{
			// support for mootools 1.1
			$(el).setHTML('');
		}
		else
		{
			// support for mootools 1.2
			$(el).set('html', '');
		}
		
		if (includeBlank)
		{
			var option = new Element('option', {
				value: ''
			});
			
			if (option.setHTML)
			{
				// support for mootools 1.1
				option.setHTML(blankLabel);
			}
			else
			{
				// support for mootools 1.2
				option.set('html', blankLabel);
			}
			
			$(el).appendChild(option);
		}
		
		// Find current selections
		var count=0;
		var currentSelection = new Array();
		for( i=0; i<$(id).options.length; i++ )
		{
			if ($(id).options[i].selected)
			{
				currentSelection[count] = $(id).options[i].value;
				count++;
			}
		}


		// Add the correct options
		for( s=0; s<currentSelection.length; s++ )
		{
			currentSelect = currentSelection[s];
			if (options[currentSelect])
			{
				var parentNode = $(el);
				var optGroup = false;
				
				if (currentSelection.length > 1)
				{
					optGroup = true;
					parentNode = new Element('optgroup', {
						label: currentSelect
					});
				}
				
				for(i=0; i<options[currentSelect].length; i++)
				{
					var option = new Element('option', {
						value: options[currentSelect][i]['value']
					});
					
					if (option.setHTML)
					{
						// support for mootools 1.1
						option.setHTML(options[currentSelect][i]['label']);
					}
					else
					{
						// support for mootools 1.2
						option.set('html', options[currentSelect][i]['label']);
					}
					
					if (options[currentSelect][i]['default'] == 'true')
					{
						option.selected = true;
					}
					
					parentNode.appendChild(option);
				}
				
				if (optGroup)
				{
					$(el).appendChild(parentNode);
				}
			}
		}
		
		if ($(el).options.length == 0)
		{
			$(el).options[0] = new Option('-', '');
		}
		
		$(el).fireEvent('change', [el, id, options]);
	}
};