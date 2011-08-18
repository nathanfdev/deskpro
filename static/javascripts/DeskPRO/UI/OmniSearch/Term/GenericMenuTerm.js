Orb.createNamespace('DeskPRO.UI.OmniSearch.Term.Tickets');

/**
 * A context is a group of search terms
 *
 * @option {jQuery} menuEl
 * @option {Object} fields
 * @option {String} inputName
 * @option {String} menuDataKey
 */
DeskPRO.UI.OmniSearch.Term.GenericMenuTerm = new Orb.Class({
	Extends: DeskPRO.UI.OmniSearch.Term.TermAbstract,

	createTermElement: function(searchBox) {
		var el = this.parent(searchBox);

		var displayValue = $('.value', el).text('');
		var prefix = searchBox.getFormTermNamePrefix();
		var dataKey = this.getDataKey();

		var fields = this.getHiddenFields();
		var inputName = this.getInputName();
		fields[inputName] = '';
		Object.each(fields, function(value, name) {
			var f = $('<input type="hidden" />');
			f.attr('name', prefix + '[' + name + ']');
			f.val(value);

			if (name == inputName) {
				f.addClass('term-val');
			}
			f.appendTo(el);
		});

		var termValue = $('input.term-val', el);

		var menu = new DeskPRO.UI.Menu({
			triggerElement: displayValue,
			menuElement: this.options.menuEl,
			onItemClicked: function(info) {
				var val = $(info.itemEl).data(dataKey);
				var display = $(info.itemEl).text().trim();
				if (!val) {
					searchBox.removeSearchTerm(el);
				} else {
					termValue.val(val);
					displayValue.text(display);
				}
			},
			onMenuClosed: function() {
				if (!termValue.val()) {
					searchBox.removeSearchTerm(el);
				}
			}
		});

		var handler = {
			afterAdd: function(displayEl) {
				menu.open({ target: displayEl });
			}
		};

		el.data('handler', handler);

		return el;
	},

	getHiddenFields: function() {
		return this.options.fields;
	},

	getInputName: function() {
		return this.options.inputName;
	},

	getDataKey: function() {
		return this.options.menuDataKey;
	}
});