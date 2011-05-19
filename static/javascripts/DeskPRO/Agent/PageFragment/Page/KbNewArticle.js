Orb.createNamespace('DeskPRO.Agent.PageFragment.Page');
DeskPRO.Agent.PageFragment.Page.KbNewArticle = new Class({

	Extends: DeskPRO.Agent.PageFragment.Basic,

	TYPENAME: 'kb_article',

	wrapper: null,

	initPage: function(el) {
		this.wrapper = el;
		var self = this;

		$('.save-trigger', this.wrapper).click(self.sendSave.bind(this));
	},

	sendSave: function() {
		var data = $(':input, select, textarea', this.wrapper).serializeArray();

		$.ajax({
			url: BASE_URL + 'agent/kb/article/new/save',
			type: 'POST',
			context: this,
			dataType: 'json',
			success: function(counts) {
				DeskPRO_Window.removePage(this);
				DeskPRO_Window.runPageRoute('kb_article:' + data.load_url);
			}
		});
	}
});