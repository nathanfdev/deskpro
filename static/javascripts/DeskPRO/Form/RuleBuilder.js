Orb.createNamespace('DeskPRO.Form');

/**
 * A rule builder is a form widget that lets you add multiple "rules" to a list.
 * A rule conists of a rule type (for example, "category"), an op ("is" or "is not" etc),
 * and then a user input or selection (the actual category choice).
 *
 * This builder handles everything except form naming (eg. rule[0][rule_type] etc), and when
 * to add rows (eg. on a button click). Some other component will figure those parts out.
 *
 * Example:
 *    <script type="text/javascript" charset="utf-8">
 *        $(document).ready(function() {
 *            var editor = new DeskPRO.Form.RuleBuilder($('#rules-tpl'));
 *            $('#add_rule_btn').data('add-count', 0).click(function() {
 *                var count = parseInt($(this).data('add-count'));
 *                var basename = 'newrule['+count+']';
 *            
 *                $(this).data('add-count', count+1);
 *            
 *                editor.addNewRow($('#rules'), basename);
 *            });
 *        });
 *    </script>
 *    <input type="button" value="Add Rule" id="add_rule_btn" />
 *    <div id="rules"></div>
 *    <div id="rules-tpl" style="display:none">
 *        <div class="row"><div class="type"></div><div class="op"></div><div class="choice"></div></div>
 *        <div class="type" title="Department" data-rule-type="department">
 *            <div class="op"><select name="op"><option value="is">is</option><option value="not">is not</option></select></div>
 *            <div class="choice"><select name="department"><option value="1">Sales</option><option value="2">Support</option></select></div>
 *        </div>
 *    </div>
 */
DeskPRO.Form.RuleBuilder = new Class({
	Implements: Events,

	ruleTpl: null,
	
	/**
	 * Select options for "rule type" we pre-built in initalize
	 */
	typeSelectHtml: null,
	
	
	/**
	 * @param {jQuery} ruleTpl This is the wrapper element that contains the templates used for each rule type
	 */
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
		var new_row = $('> .row', this.ruleTpl).children().clone();
		
		// Add select
		$('.type:first', new_row).html(this.typeSelectHtml);
		var select = $('.type:first > select', new_row);

		// Update its name
		if (formBaseName) {
			new_row.data('form-base-name', formBaseName);
			this.updateFormName(new_row, formBaseName);
		}
		
		if (existing) {
			select.val(existing.rule_type);
			this.handleSelectChange(new_row);
			$('.op:first select', new_row).val(existing.op);
			
			Object.each(existing.choice, function(val, name) {
				var el = $('[name="'+name+'"], [name$="\['+name+'\]"]', new_row).first().val(val);
			}, this);
		}
		
		// Handle when its type is changed
		select.change((function() {
			this.handleSelectChange(new_row);
		}).bind(this));
		
		$(addToEl).append(new_row);
		
		this.fireEvent('newRow', [new_row, addToEl, existing]);
		
		return new_row;
	},
	
	
	
	/**
	 * When a select element is changed we need to update the op and choices.
	 *
	 * @param {jQuery} row The row that we need to update
	 */
	handleSelectChange: function(row) {
		var rule_type = $('.type:first > select', row).val();
		
		var rule_tpl = $('> .type[data-rule-type="'+rule_type+'"]', this.ruleTpl);
		var op = $('> .op:first', rule_tpl).children().clone();
		var choice = $('> .choice:first', rule_tpl).children().clone();
		
		$('.op:first', row).empty().append(op);
		$('.choice:first', row).empty().append(choice);
		
		if (row.data('form-base-name')) {
			this.updateFormName($('.op:first', row), row.data('form-base-name'));
			this.updateFormName($('.choice:first', row), row.data('form-base-name'));
		}
		
		this.fireEvent('selectChange', [row, rule_type]);
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