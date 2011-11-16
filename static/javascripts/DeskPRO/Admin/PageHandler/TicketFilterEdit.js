Orb.createNamespace('DeskPRO.Admin.PageHandler');

DeskPRO.Admin.PageHandler.TicketFilterEdit = new Class({
	Extends: DeskPRO.Admin.PageHandler.Basic,

	TYPE: 'TicketFilterEdit',
	filter_id: 0,
	actionsEditor: null,
	criteriaEditor: null,

	initialize: function(filter_id) {
		this.parent();
		this.filter_id = filter_id;
	},

	initPage: function() {
		var self = this;
		$('.save-trigger').on('click', function() {
			$('form:first').submit();
		});

		// Criteria builder
		this.criteriaEditor = new DeskPRO.Form.RuleBuilder($('.criteria-tpl'));

		$('.criteria-form .add-term').data('add-count', 0).on('click', function() {
			var count = parseInt($(this).data('add-count'));
			var basename = 'terms['+count+']';

			$(this).data('add-count', count+1);

			self.criteriaEditor.addNewRow($('.criteria-form .search-terms'), basename);
		});

		if (this.init_options_callback) {
			this.init_options_callback();
		}
	}
});
