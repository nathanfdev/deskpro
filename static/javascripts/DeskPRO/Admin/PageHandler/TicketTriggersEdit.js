Orb.createNamespace('DeskPRO.Admin.PageHandler');

DeskPRO.Admin.PageHandler.TicketTriggersEdit = new Class({
	Extends: DeskPRO.Admin.PageHandler.Basic,

	TYPE: 'TicketTriggersEdit',
	trigger_id: 0,
	actionsEditor: null,
	criteriaEditor: null,

	initialize: function(trigger_id) {
		this.parent();
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

		$('.criteria-form .add-term').data('add-count', 0).click(function() {
			var count = parseInt($(this).data('add-count'));
			var basename = 'terms['+count+']';

			$(this).data('add-count', count+1);

			self.criteriaEditor.addNewRow($('.criteria-form .search-terms'), basename);
		});

		// Actions builder
		this.actionsEditor = new DeskPRO.Form.RuleBuilder($('.actions-tpl'));
		this.actionsEditor.addEvent('newRow', function(new_row) {
			$('.remove', new_row).click(function() {
				new_row.remove();
			});

			var a = $('.use_custom_template', new_row);

			if (a.length) {
				var id = Orb.getUniqueId('id');
				$(new_row).attr('id', id);

				var href = $(a).attr('href');
				href = href.replace('_opener_id_', id);
				href = href.replace('_template_', escape($('.custom_template_name', new_row).val() || ''));
				href = href.replace('_template_orig_', escape($('.custom_template_default', new_row).val() || ''));

				$(a).attr('href', href);

				self.initPopoutTriggers(new_row);
			}
		});

		$('.actions-form .add-term').data('add-count', 0).click(function() {
			var count = parseInt($(this).data('add-count'));
			var basename = 'actions['+count+']';

			$(this).data('add-count', count+1);

			self.actionsEditor.addNewRow($('.actions-form .search-terms'), basename);
		});

		if (this.init_options_callback) {
			this.init_options_callback();
		}
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