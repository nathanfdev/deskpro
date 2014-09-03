Orb.createNamespace('DeskPRO.Agent.PageFragment.Page');
DeskPRO.Agent.PageFragment.Page.DpNews = new Orb.Class({

	Extends: DeskPRO.Agent.PageFragment.Basic,

	initializeProperties: function() {
		this.parent();
		this.TYPENAME = 'dp_news';
	},

	initPage: function(el) {
		var self = this;
		this.el = el;

		this.getEl('dismiss').on('click', function() {
			$(this).hide();
			$.ajax({
				url: BASE_URL + 'agent/misc/view-dp-news/'+self.meta.dpNewsId+'/dismiss',
				dataType: 'json',
				type: 'POST'
			});
		});
	}
});
