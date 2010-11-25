Orb.createNamespace('DeskPRO.Form');

DeskPRO.Form.RuleBuilder = new Class({
	ruleTpl: null,
	
	typeSelectHtml: null,
	
	initialize: function(ruleTpl) {
		this.ruleTpl = ruleTpl;
		
		this.typeSelectHtml = ['<select name="rule_type"><option value=""></option>'];
		$('> .type', this.ruleTpl).each((function(i,el) {
			this.typeSelectHtml.push('<option value="' + $(el).data('rule-type') + '">' + $(el).attr('title') + '</option>');
		}).bind(this));
		this.typeSelectHtml.push('</select>');
		this.typeSelectHtml = this.typeSelectHtml.join('');
	},
	
	
	
	/**
	 * Add a new rule row
	 *
	 * @param {jQuery} addToEl The element to append the new rule to
	 * @param {String} formBaseName The base name for the form. For example, newrule[0], and after you might use newrule[1] etc.
	 * @param {Object} existing Existing data to set
	 * @return {jQuery} The newly added row
	 */
	addNewRow: function(addToEl, formBaseName, existing) {
		var new_row = $('> .row', this.ruleTpl).clone();
		
		// Add select
		$('> .type', new_row).html(this.typeSelectHtml);
		var select = $('> .type > select', new_row);

		// Update its name
		if (formBaseName) {
			new_row.data('form-base-name', formBaseName);
			this.updateFormName(new_row, formBaseName);
		}
		
		if (existing) {
			select.val(existing.rule_type);
			this.handleSelectChange(new_row);
			$('> .op select', new_row).val(existing.op);
			
			Object.each(existing.choice, function(val, name) {
				var el = $('[name="'+name+'"], [name$="['+name+']"]').first();
				
				if (el.is('select')) {
					$('option[value="'+val+'"]', el).attr('selected', true);
				}
			}, this);
		}
		
		// Handle when its type is changed
		select.change((function() {
			this.handleSelectChange(new_row);
		}).bind(this));
		
		$(addToEl).append(new_row);
		
		return new_row;
	},
	
	
	
	/**
	 * When a select element is changed we need to update the op and choices.
	 *
	 * @param {jQuery} row The row that we need to update
	 */
	handleSelectChange: function(row) {
		var rule_type = $('> .type > select', row).val();
		
		var rule_tpl = $('> .type[data-rule-type="'+rule_type+'"]', this.ruleTpl);
		var op = $('> .op', rule_tpl).children().clone();
		var choice = $('> .choice', rule_tpl).children().clone();
		
		$('> .op', row).append(op);
		$('> .choice', row).append(choice);
		
		if (row.data('form-base-name')) {
			this.updateFormName($('> .op', row), row.data('form-base-name'));
			this.updateFormName($('> .choice', row), row.data('form-base-name'));
		}
	},
	
	
	
	/**
	 * This updates the form name to prepend a basename, and turns it into an array usable
	 * by php. For example, if the name was before rule_type and formbaseName is newrule[1],
	 * the new form name is newrule[1][rule_type].
	 * 
	 * @param {jQuery} el The element to look within to change ALL names of
	 * @param {String} formBaseName The base form name to set
	 */
	updateFormName: function(el, formBaseName) {
		$('[name]', el).each(function() {
			var name = $(this).attr('name');
			name = name.replace(/^([\w\d]*)/, '[$1]');
			name = formBaseName + name;
			
			$(this).attr('name', name);
		});
	}
});