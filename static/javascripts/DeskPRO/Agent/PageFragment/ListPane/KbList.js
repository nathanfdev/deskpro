Orb.createNamespace('DeskPRO.Agent.PageFragment.ListPane');

DeskPRO.Agent.PageFragment.ListPane.KbList = new Class({
	Extends: DeskPRO.Agent.PageFragment.ListPane.Basic,

	wrapper: null,

	initPage: function(el) {
		this.wrapper = el;

		/*
		this.listSearchForm = new DeskPRO.Agent.PageHelper.ListSearchForm(this, {
			form: $('form.kb-search-form', this.topSection),
			context: this.topSection,
			searchData: $('.search-form-data:first', this.topSection)
		});

		this.listSearchForm.addEvent('searchSubmit', function(url, data) {
			DeskPRO_Window.loadListPane(url, { postData: data });
		});
		*/

		this.displayOptions = new DeskPRO.Agent.PageHelper.DisplayOptions(this, {
			prefId: 'kb-filter',
			resultId: this.meta.resultId,
			refreshUrl: this.meta.refreshUrl
		});

		this.selectionBar = new DeskPRO.Agent.PageHelper.SelectionBar(this, {

		});

		this.initRoutesOnCollection($('.with-route', el));
	}
});