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