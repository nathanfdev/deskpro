Orb.createNamespace('DeskPRO.UI.OmniSearch.Term.Tickets');

/**
 * A term where the value is a text input box
 *
 * @option {String|jQuery} menuEl The menu element
 */
DeskPRO.UI.OmniSearch.Term.MenuTermAbstract = new Orb.Class({
	Extends: DeskPRO.UI.OmniSearch.Term.TermAbstract,

	init: function() {

	},

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
					el.remove();
				} else {
					termValue.val(val);
					displayValue.text(display);
				}
			},
			onMenuClosed: function() {
				if (!termValue.val()) {
					el.remove();
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
		return {};
	},

	getInputName: function() {
		return 'input';
	},

	getDataKey: function() {
		return 'prop-val';
	}
});