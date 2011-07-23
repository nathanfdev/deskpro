Orb.createNamespace('DeskPRO.Agent.PageFragment.ListPane');

DeskPRO.Agent.PageFragment.ListPane.DownloadList = new Class({
	Extends: DeskPRO.Agent.PageFragment.ListPane.Basic,

	wrapper: null,

	initPage: function(el) {
		this.wrapper = el;
		this.initRoutesOnCollection($('.with-route', el));

		this.topSection = $('.list-top-area:first', this.wrapper);

		this.listSearchForm = new DeskPRO.Agent.PageHelper.ListSearchForm(this, {
			form: $('form.download-search-form', this.topSection),
			context: this.topSection,
			searchData: $('.search-form-data:first', this.topSection)
		});

		this.listSearchForm.addEvent('searchSubmit', function(url, data) {
			DeskPRO_Window.loadListPane(url, { postData: data });
		});
	}
});