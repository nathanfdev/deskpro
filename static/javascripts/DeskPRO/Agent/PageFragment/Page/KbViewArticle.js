Orb.createNamespace('DeskPRO.Agent.PageFragment.Page');
DeskPRO.Agent.PageFragment.Page.KbViewArticle = new Class({

	Extends: DeskPRO.Agent.PageFragment.Basic,

	TYPENAME: 'kb_article_view',

	wrapper: null,
	article_id: null,

	initPage: function(el) {
		this.wrapper = el;

		this.article_id = this.getMetaData('article_id');

		var self = this;

		var self = this;
		$('.edit-trigger', this.wrapper).click(function() {
			DeskPRO_Window.runPageRoute('kb_article_edit:' + BASE_URL + 'agent/kb/article/' + self.article_id);
			DeskPRO_Window.removePage(self);
		});

		$('.validate-trigger', this.wrapper).click(function() {
			DeskPRO_Window.runPageRoute('kb_article_edit:' + BASE_URL + 'agent/kb/article/' + self.article_id + '?do_validate=1');
			DeskPRO_Window.removePage(self);
		});
	}
});