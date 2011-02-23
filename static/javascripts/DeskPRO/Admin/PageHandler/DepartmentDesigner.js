Orb.createNamespace('DeskPRO.Admin.PageHandler');

DeskPRO.Admin.PageHandler.DepartmentDesigner = new Class({
	Extends: DeskPRO.Admin.PageHandler.Basic,

	department_id: 0,
	initialize: function(department_id) {
		this.department_id = department_id;

		$('#display_item').template('display_item');
		DeskPRO_Window.getMessageBroker().addMessageListener('field.change', this.fetchNewlyCreatedField.bind(this));
	},

	initPage: function() {
		this.initPopoutTriggers();

		var self = this;
		$('.available-display-items .add-trigger').live('click', function() {
			var el = $(this);
			var parent = el.parent();

			var itemName = parent.data('item-name');
			var itemId = parent.data('item-id');
			var idClass = itemId.replace(/[^a-zA-Z0-9_]/g, '_');
			var rendered = false;
			if ($('div.rendered', parent).length) {
				rendered = $('div.rendered', parent).clone();
			}

			// So we dont add it twice, hide it
			$('.' + idClass).hide();

			self.addDisplayItem(itemName, itemId, rendered);
		});
	},

	addDisplayItem: function(itemName, itemId, rendered) {
		var data = {name: itemName, itemId: itemId };

		var item = $.tmpl('display_item', data).appendTo('#display_item_list');
		if (rendered) {
			$('div.rendered', item).replaceWith(rendered);
			rendered.show();
		} else {
			$('div.rendered', item).remove();
		}

		this.initDisplayItem(item, itemId);
	},

	initDisplayItem: function(itemEl, itemId) {
		$('.toggle-rules', itemEl).click(function() {
			var norules = $('.no-rules-wrap', itemEl);
			var rules = $('.rules-wrap', itemEl);

			var link_on = $('.toggle-rules.on', itemEl);
			var link_off = $('.toggle-rules.off', itemEl);
			var f = $('.display_items_withrules', itemEl);

			if (f.val() == '1') {
				f.val('0');
				rules.hide();
				link_off.hide();
				norules.show();
				link_on.show();
			} else {
				f.val('1');
				norules.hide();
				link_on.hide();
				rules.show();
				link_off.show();
			}
		});

		var editor = new DeskPRO.Form.RuleBuilder($('#criteria_tpl'));
		editor.addEvent('newRow', function(new_row) {
			$('.remove', new_row).click(function() {
				new_row.remove();
			});
		});
		var to_el = $('.search-form.ruletype-all .rule-list', itemEl);

		$('.search-form.ruletype-all .add-term').data('add-count', 0).click(function() {
			var count = parseInt($(this).data('add-count'));
			var basename = 'terms_all['+itemId+']['+count+']';
			$(this).data('add-count', count+1);
			editor.addNewRow(to_el, basename);
		});

		var editor2 = new DeskPRO.Form.RuleBuilder($('#criteria_tpl'));
		editor2.addEvent('newRow', function(new_row) {
			$('.remove', new_row).click(function() {
				new_row.remove();
			});
		});
		var to_el2 = $('.search-form.ruletype-any .rule-list', itemEl);

		$('.search-form.ruletype-any .add-term').data('add-count', 0).click(function() {
			var count = parseInt($(this).data('add-count'));
			var basename = 'terms_any['+itemId+']['+count+']';
			$(this).data('add-count', count+1);
			editor2.addNewRow(to_el2, basename);
		});
	},

	fetchNewlyCreatedField: function (info) {
		var field_id = info.field_id;
		$.ajax({
			url: DeskPRO_Window.getUrl('admin_departments_designer_ajaxfetchfield', {field_id: field_id}),
			dataType: 'json',
			type: 'GET',
			success: function(data) {
				var ul = $('ul.field-list:first');
				var last = $('li:last', ul);
				var exist = $('li.field-' + field_id);
				if (exist.length) {
					exist.replaceWith(data.html);
				} else {
					last.before(data.html);
				}
			}
		});
	}
});