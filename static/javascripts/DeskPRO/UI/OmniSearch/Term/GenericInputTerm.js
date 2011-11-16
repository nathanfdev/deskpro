Orb.createNamespace('DeskPRO.UI.OmniSearch.Term.Tickets');

/**
 * A context is a group of search terms
 */
DeskPRO.UI.OmniSearch.Term.GenericInputTerm = new Orb.Class({
	Extends: DeskPRO.UI.OmniSearch.Term.TermAbstract,

	createTermElement: function(searchBox) {
		var tpl = document.getElementById('omnisearch_term_input_tpl').innerHTML;
		var el = $(tpl);
		var input = $('input', el);
		var displayValue = $('.display-value', el);
		var prefix = searchBox.getFormTermNamePrefix();

		$('.label', el).text(this.getLabel());
		displayValue.text('double-click to edit');

		el.addClass('edit');

		el.on('click', function() {
			el.addClass('edit');
			input.focus();
		});

		var endEdit = function() {
			el.removeClass('edit');
			var val = input.val().trim();
			displayValue.text(val);

			if (!val.length) {
				searchBox.removeSearchTerm(el);
			}

			searchBox.fireEvent('termInputDone', [el]);
		};

		input.on('blur', endEdit).on('keypress', function(ev) {
			if (ev.which == 13 || ev.which == 9) {
				endEdit();
			}
		});

		input.attr('name', prefix + '[' + this.getInputName() + ']');

		Object.each(this.getHiddenFields(), function(value, name) {
			var f = $('<input type="hidden" />');
			f.attr('name', prefix + '[' + name + ']');
			f.val(value);
			f.appendTo(el);
		});

		var handler = {
			afterAdd: function(el) {
				input.focus();
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
	}
});
