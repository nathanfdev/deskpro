Orb.createNamespace('DeskPRO.Agent.PageFragment.ListPane');

DeskPRO.Agent.PageFragment.ListPane.IdeaFilter = new Class({
	Extends: DeskPRO.Agent.PageFragment.ListPane.Basic,

	wrapper: null,
	filterSearchForm: null,

	initPage: function(el) {
		var self = this;
		this.wrapper = el;

		this.displayOptions = new DeskPRO.Agent.PageHelper.DisplayOptions(this, {
			prefId: 'idea-filter',
			resultId: this.meta.resultId,
			refreshUrl: this.meta.refreshUrl
		});

		this.selectionBar = new DeskPRO.Agent.PageHelper.SelectionBar(this, {

		});

		this.initRoutesOnCollection($('.with-route', el));

		this.listWrapper = $('section.idea-simple-list', this.wrapper);

		this.relatedContentList = new DeskPRO.Agent.PageHelper.RelatedContentList(this, {
			contentListEl: this.listWrapper
		});
	}
});
