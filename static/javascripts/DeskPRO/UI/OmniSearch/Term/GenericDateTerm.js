Orb.createNamespace('DeskPRO.UI.OmniSearch.Term.Tickets');

/**
 * A context is a group of search terms
 *
 * @option {jQuery} menuEl
 * @option {Object} fields
 * @option {String} inputName
 * @option {String} menuDataKey
 */
DeskPRO.UI.OmniSearch.Term.GenericDateTerm = new Orb.Class({
	Extends: DeskPRO.UI.OmniSearch.Term.TermAbstract,

	createTermElement: function(searchBox) {
		var tpl = document.getElementById('omnisearch_term_date_tpl').innerHTML;
		var el = $(tpl);

		var displayValue = $('.display-value', el).text('');
		var prefix = searchBox.getFormTermNamePrefix();

		var fields = this.getHiddenFields();
		Object.each(fields, function(value, name) {
			var f = $('<input type="hidden" />');
			f.attr('name', prefix + '[' + name + ']');
			f.val(value);

			f.appendTo(el);
		});

		var dateChooser = new DeskPRO.UI.DateChooser({
			rowEl: el
		});

		var handler = {
			afterAdd: function(displayEl) {
				dateChooser.open();
			},
			destroy: function() {
				dateChooser.destroy();
			}
		};

		el.data('handler', handler);

		return el;
	},

	getDateChooser: function() {
		return this.options.dateChooser;
	},

	getHiddenFields: function() {
		return this.options.fields;
	},

	getInputName: function() {
		return this.options.inputName;
	}
});