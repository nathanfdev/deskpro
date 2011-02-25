Orb.createNamespace('DeskPRO.Admin.PageHandler');

DeskPRO.Admin.PageHandler.TicketTriggersEdit = new Class({
	Extends: DeskPRO.Admin.PageHandler.Basic,

	trigger_id: 0,
	actionsEditor: null,
	criteriaEditor: null,

	initialize: function(trigger_id) {
		this.trigger_id = trigger_id;
	},

	initPage: function() {
		var self = this;
		$('.save-trigger').click(function() {
			$('form:first').submit();
		});
		$('.cancel-trigger').click(function() {
			self.closeThisPopout();
		});

		// Criteria builder
		this.criteriaEditor = new DeskPRO.Form.RuleBuilder($('.criteria-tpl'));
		this.criteriaEditor.addEvent('newRow', function(new_row) {
			$('.remove', new_row).click(function() {
				new_row.remove();
			});
		});

		var to_el = $('.criteria-form .search-terms');

		$('.criteria-form .add-term').data('add-count', 0).click(function() {
			var count = parseInt($(this).data('add-count'));
			var basename = 'actions['+count+']';

			$(this).data('add-count', count+1);

			self.criteriaEditor.addNewRow(to_el, basename);
		});

		// Actions builder
		this.actionsEditor = new DeskPRO.Form.RuleBuilder($('.actions-tpl'));
		this.actionsEditor.addEvent('newRow', function(new_row) {
			$('.remove', new_row).click(function() {
				new_row.remove();
			});
		});

		to_el = $('.actions-form .search-terms');

		$('.actions-form .add-term').data('add-count', 0).click(function() {
			var count = parseInt($(this).data('add-count'));
			var basename = 'actions['+count+']';

			$(this).data('add-count', count+1);

			self.actionsEditor.addNewRow(to_el, basename);
		});
	},

	updateParentListRow: function(row_html) {
		var parent_win = this.getOpenerDeskPRO();
		if (!parent_win) return;

		var data = {};
		data['item_selector'] = 'li.trigger-' + this.trigger_id;
		data['row_html'] = row_html;

		parent_win.getMessageBroker().sendMessage('list.change', data);
	}
});