Orb.createNamespace('DeskPRO.Form');

/**
 * A rule builder is a form widget that lets you add multiple "rules" to a list.
 * A rule conists of a rule type (for example, "category"), an op ("is" or "is not" etc),
 * and then a user input or selection (the actual category choice).
 *
 * This builder handles everything except form naming (eg. rule[0][type] etc), and when
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

		this.typeSelectHtml = ['<select name="type" class="type">'];
		$('> .type', this.ruleTpl).each((function(i,el) {
			this.typeSelectHtml.push('<option value="' + $(el).data('rule-type') + '">' + $(el).attr('title') + '</option>');
		}).bind(this));
		this.typeSelectHtml.push('</select>');
		this.typeSelectHtml = this.typeSelectHtml.join('');

		
	},



	/**
	 * Add a new rule row
	 *
	 * @param  {jQuery} addToEl The element to append the new rule to
	 * @param  {String} formBaseName The base name for the form. For example, newrule[0], and after you might use newrule[1] etc.
	 * @param  {Object} existing Existing data to set
	 * @return {jQuery} The newly added row
	 */
	addNewRow: function(addToEl, formBaseName, existing) {
		var new_row = $('> .row', this.ruleTpl).children().clone();

		// Add select
		$('.type:first', new_row).html(this.typeSelectHtml);
		var select = $('select.type:first', new_row);

		var menu = new DeskPRO.UI.Menu({
			menuElement: select
		});
		console.log(menu);

		// Update its name
		if (formBaseName) {
			new_row.data('form-base-name', formBaseName);
			this.updateFormName(new_row, formBaseName);
		}

		var opt = false;
		if (existing) {
			opt = $('option[value="' + existing.type + '"]:first', select);
			opt.attr('selected', true);

			this.handleSelectChange(new_row);
			$('.op:first select', new_row).val(existing.op).addClass('op');

			if (typeof existing.options == 'string' || typeof existing.options == 'number' || typeOf(existing.options) != 'object') {
				// If its just one item, then we'll just assume its the first field
				$(':input, textarea, select', new_row).filter(':not(.op, .type)').first().val(existing.options);
			} else {
				// Otherwise we'll assume its a k=>v array
				Object.each(existing.options, function(val, name) {
					if (!name || !name.length) return;

					var name_safe = name.replace(/\[/, '\\[').replace(/\]/, '\\]');
					if (typeof val == 'string' || typeof val == 'number') {
						var el = $('[name="'+name_safe+'"], [name$="'+this.makeArrayName(name,true)+'"]', new_row).first().val(val);
					} else if (typeOf(val) == 'object') {
						Object.each(val, function(subval, subname) {
							var sub_name = name_safe + "["+subname+"]";
							var sub_name_safe = name_safe + "\\["+subname+"\\]";
							var el = $('[name="'+sub_name_safe+'"], [name$="'+this.makeArrayName(sub_name,true)+'"]', new_row).first().val(subval);
						}, this);
					} else if (typeOf(val) == 'array') {
						Array.each(val, function(subval) {
							var el = $('option[value="'+subval+'"]', new_row).first().get(0);
							el.selected = true;
						}, this);
					} else {
						var el = $('[name="'+name_safe+'"], [name$="'+this.makeArrayName(name,true)+'"]', new_row).first().val(val);
					}
				}, this);
			}
		}

		// Handle when its type is changed
		select.change((function() {
			this.handleSelectChange(new_row);
		}).bind(this));

		$(addToEl).append(new_row);

		if (opt) {
			opt.attr('selected', true);
		}

		this.fireEvent('newRow', [new_row, addToEl, existing]);

		return new_row;
	},



	/**
	 * When a select element is changed we need to update the op and choices.
	 *
	 * @param {jQuery} row The row that we need to update
	 */
	handleSelectChange: function(row) {
		var type = $('.type:first > select', row).val();

		var rule_tpl = $('> .type[data-rule-type="'+type+'"]', this.ruleTpl);

		var op = $('> .op:first', rule_tpl).children().clone();

		var choice = $('> .options:first', rule_tpl).clone();
		choice.css('display', 'inline');

		$('.op:first', row).empty().append(op);
		$('.options:first', row).empty().append(choice);

		if (op.is('select')) {
			var opMenu = new DeskPRO.UI.Menu({
				menuElement: op
			});
		}

		var choiceSel = $('select', choice);
		if (choiceSel.length) {
			var choiceMenu = new DeskPRO.UI.Menu({
				menuElement: choiceSel
			});
		}

		if (row.data('form-base-name')) {
			this.updateFormName($('.op:first', row), row.data('form-base-name'));
			this.updateFormName($('.options:first', row), row.data('form-base-name'));
		}

		this.fireEvent('selectChange', [row, type]);
	},



	/**
	 * This updates the form name to prepend a basename, and turns it into an array usable
	 * by php. For example, if the name was before type and formbaseName is newrule[1],
	 * the new form name is newrule[1][type].
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
	},

	makeArrayName: function(name, safe) {
		if (name.indexOf('[') === -1) {
			name = '[' + name + ']';
		}  else {
			name = name.replace(/^([\w\d]+)\[(.*?)$/, '[$1][$2');
		}

		if (safe) {
			name = name.replace(/\[/g, '\\[').replace(/\]/g, '\\]');
		}

		return name;
	}
});