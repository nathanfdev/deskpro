Orb.createNamespace('DeskPRO.Agent.PageFragment.ListPane');

DeskPRO.Agent.PageFragment.ListPane.IdeaFilter = new Class({
	Extends: DeskPRO.Agent.PageFragment.ListPane.Basic,

	wrapper: null,
	filterSearchForm: null,

	initPage: function(el) {
		this.wrapper = el;

		this.initRoutesOnCollection($('.with-route', el));

		this.filterSearchForm = $('.idea-filter-form', el);
		if (this.filterSearchForm) {
			this._initFilterSearch();
		} else {
			this.filterSearchForm = null;
		}

		// When we get these messages we can remove them from the list
		if (this.meta.isValidating)	{
			DeskPRO_Window.getMessageBroker().addMessageListener('validating-ideas.deleted', this.handleRemoveIdea.bind(this));
			DeskPRO_Window.getMessageBroker().addMessageListener('validating-ideas.approved', this.handleRemoveIdea.bind(this));
		}
	},

	handleRemoveIdea: function(info) {
		$('.idea-' + info.idea_id, this.wrapper).fadeOut();
	},

	_initFilterSearch: function() {
		var self = this;
		$(':input', this.filterSearchForm).change(function() {
			$('.submit-row', self.filterSearchForm).show();
		});

		$('.submit-trigger', this.filterSearchForm).click(function(ev) {

			// Its a real form, prevent submission
			ev.preventDefault();

			var data = $('select, :input', self.filterSearchForm).serializeArray();

			var url = self.meta.submitFilterUrl;
			var routeData = {
				postData: data
			};
			DeskPRO_Window.loadListPane(url, routeData);
		});
		
	}
});