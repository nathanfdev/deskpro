Orb.createNamespace('DeskPRO.Agent.PageFragment.Page');
DeskPRO.Agent.PageFragment.Page.KbEditArticle = new Class({

	Extends: DeskPRO.Agent.PageFragment.Basic,

	TYPENAME: 'kb_article',

	wrapper: null,
	article_id: null,

	initPage: function(el) {
		this.wrapper = el;

		this.article_id = this.getMetaData('article_id');

		var self = this;

		$('.save-trigger', this.wrapper).click(self.sendSave.bind(this));
	},

	sendSave: function() {
		var data = $(':input, select, textarea', this.wrapper).serializeArray();

		$.ajax({
			url: BASE_URL + 'agent/kb/article/'+this.article_id+'/save',
			type: 'POST',
			context: this,
			dataType: 'json',
			data: data,
			success: function(counts) {

			}
		});
	}
});