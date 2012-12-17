Orb.createNamespace('DeskPRO.Agent.PageFragment.ListPane');

DeskPRO.Agent.PageFragment.ListPane.TwitterFollowers = new Orb.Class({
    Extends: DeskPRO.Agent.PageFragment.ListPane.Basic,

    initPage: function(el) {
        this.wrapper = $(el);
		this.content = $('.content', this.wrapper);
		var self = this;

		this.meta.fetchResultsUrl = this.meta.listUrl;

		var helper = new DeskPRO.Agent.PageHelper.Twitter(this.content, this, {
			messageUrl: this.getMetaData('saveUserMessageUrl')
		});

		this.content.find('textarea').TextAreaExpander();

		var opt = {
			perPage: this.meta.perPage || 25,
			currentPage: this.meta.currentPage,
			totalCount: this.meta.totalCount,
			resultRowSelector: 'article.twitter-user',
			resultsContainer: this.content
		};
		this.resultsHelper = new DeskPRO.Agent.PageHelper.Results(this, opt);
		this.ownObject(this.resultsHelper);
    }

});
