Orb.createNamespace('DeskPRO.Admin.PageHandler');

DeskPRO.Admin.PageHandler.DepartmentDesigner = new Class({
	Extends: DeskPRO.Admin.PageHandler.Basic,

	department_id: 0,
	initialize: function(department_id) {
		this.department_id = department_id;

		$('#display_item').template('display_item');
	},

	initPage: function() {
		var self = this;
		$('.available-display-items .add-trigger').click(function() {
			var el = $(this);
			var parent = el.parent();

			var itemName = parent.data('item-name');
			var itemId = parent.data('item-id');
			var idClass = itemId.replace(/[^a-zA-Z0-9_]/, '_');

			// So we dont add it twice, hide it
			$('.' + idClass).hide();

			self.addDisplayItem(itemName, itemId);
		});
	},

	addDisplayItem: function(itemName, itemId) {
		var data = {name: name, itemId: itemId };

		var item = $.tmpl('display_item', data).appendTo('#display_item_list');

		this.initDisplayItem(item, itemId);
	},

	initDisplayItem: function(itemEl, itemId) {
		$('.toggle-rules', itemEl).click(function() {
			var norules = $('.no-rules-wrap', itemEl);
			var rules = $('.rules-wrap', itemEl);

			if (norules.is(':hidden')) {
				rules.hide();
				norules.show();
			} else {
				norules.hide();
				rules.show();
			}
		});

		var editor = new DeskPRO.Form.RuleBuilder($('#criteria_tpl'));
		editor.addEvent('newRow', function(new_row) {
			$('.remove', new_row).click(function() {
				new_row.remove();
			});
		});
		var to_el = $('.search-form .rule-list', itemEl);

		$('.search-form .add-term').data('add-count', 0).click(function() {
			var count = parseInt($(this).data('add-count'));
			var basename = 'terms['+itemId+']['+count+']';
			$(this).data('add-count', count+1);
			editor.addNewRow(to_el, basename);
		});
	}
});