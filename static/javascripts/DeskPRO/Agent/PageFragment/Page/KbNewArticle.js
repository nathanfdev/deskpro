Orb.createNamespace('DeskPRO.Agent.PageFragment.Page');
DeskPRO.Agent.PageFragment.Page.KbNewArticle = new Class({

	Extends: DeskPRO.Agent.PageFragment.Basic,

	TYPENAME: 'kb_article_new',

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
			data: data,
			success: function(data) {
				if (data.pending_article_id) {
					DeskPRO_Window.getMessageBroker().sendMessage('kb.pending_article_removed', {pending_article_id: data.pending_article_id});
				}
				DeskPRO_Window.runPageRoute('kb_article_edit:' + data.load_url);
				DeskPRO_Window.removePage(this);
			}
		});
	}
});