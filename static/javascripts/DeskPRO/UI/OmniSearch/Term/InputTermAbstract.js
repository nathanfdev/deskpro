Orb.createNamespace('DeskPRO.UI.OmniSearch.Term.Tickets');

/**
 * A term where the value is a text input box
 */
DeskPRO.UI.OmniSearch.Term.InputTermAbstract = new Orb.Class({
	Extends: DeskPRO.UI.OmniSearch.Term.TermAbstract,

	init: function() {

	},

	createTermElement: function(searchBox) {
		var tpl = document.getElementById('omnisearch_term_input_tpl').innerHTML;
		var el = $(tpl);
		var input = $('input', el);
		var displayValue = $('.display-value', el);
		var prefix = searchBox.getFormTermNamePrefix();

		$('.label', el).text(this.getLabel());
		displayValue.text('double-click to edit');

		el.addClass('edit');

		el.dblclick(function() {
			el.addClass('edit');
			input.focus();
		});

		var endEdit = function() {
			el.removeClass('edit');
			displayValue.text(input.val().trim());

			searchBox.fireEvent('termInputDone');
		};

		input.blur(endEdit).keypress(function(ev) {
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

		return el;
	},

	getHiddenFields: function() {
		return {};
	},

	getInputName: function() {
		return 'input';
	}
});