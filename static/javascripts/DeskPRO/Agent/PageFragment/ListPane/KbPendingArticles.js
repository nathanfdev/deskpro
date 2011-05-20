Orb.createNamespace('DeskPRO.Agent.PageFragment.ListPane');

DeskPRO.Agent.PageFragment.ListPane.KbPendingArticles = new Class({
	Extends: DeskPRO.Agent.PageFragment.ListPane.Basic,

	wrapper: null,

	initPage: function(el) {
		this.wrapper = el;

		this.initRoutesOnCollection($('.with-route', el));

		$('.new-pending-article-trigger', el).click(this.showAddDlg.bind(this));
	},

	showAddDlg: function() {
		var addDlg = this.getAddDlg();
		addDlg.openOverlay();
	},

	getAddDlg: function() {
		if (this.addDlg) return this.addDlg;

		var el = $('.add-dlg:first', this.wrapper);
		this.addDlg = new DeskPRO.UI.Overlay({
			contentElement: el
		});

		$('.save-trigger', el).click(this.saveNewPendingArticle.bind(this));

		return this.addDlg;
	},

	saveNewPendingArticle: function() {
		var data = [];
		data.push({
			name: 'comment',
			value: $('.comment', this.addDlg.elements.wrapperOuter).val().trim()
		});

		$.ajax({
			url: BASE_URL + 'agent/kb/pending-articles/new',
			type: 'POST',
			data: data,
			context: this,
			dataType: 'json',
			success: function(counts) {

			}
		});
	}
});