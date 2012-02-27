Orb.createNamespace('DeskPRO.Agent.PageFragment.ListPane');

DeskPRO.Agent.PageFragment.ListPane.NewsList = new Orb.Class({
	Extends: DeskPRO.Agent.PageFragment.ListPane.Basic,

	initializeProperties: function() {
		this.parent();
		this.wrapper = null;
	},

	initPage: function(el) {
		this.wrapper = el;

		this.displayOptions = new DeskPRO.Agent.PageHelper.DisplayOptions(this, {
			prefId: 'news-filter',
			resultId: this.meta.resultId,
			refreshUrl: this.meta.refreshUrl
		});
		this.ownObject(this.displayOptions);

		this.selectionBar = new DeskPRO.Agent.PageHelper.SelectionBar(this, {

		});
		this.ownObject(this.selectionBar);

		this.listWrapper = $('section.news-simple-list', this.wrapper);

		this.relatedContentList = new DeskPRO.Agent.PageHelper.RelatedContentList(this, {
			contentListEl: this.listWrapper
		});
		this.ownObject(this.relatedContentList);

		this.enableHighlightOpenRows('news', 'news_id', 'article.news-');

		this.listNav = new DeskPRO.Agent.PageHelper.ListNav(this);
	}
});
