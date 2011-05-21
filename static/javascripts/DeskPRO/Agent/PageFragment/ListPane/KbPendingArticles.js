Orb.createNamespace('DeskPRO.Agent.PageFragment.ListPane');

DeskPRO.Agent.PageFragment.ListPane.KbPendingArticles = new Class({
	Extends: DeskPRO.Agent.PageFragment.ListPane.Basic,

	wrapper: null,

	initPage: function(el) {
		this.wrapper = el;

		this.initRoutesOnCollection($('.with-route', el));

		$('.new-pending-article-trigger', el).click(this.showAddDlg.bind(this));
		
		var self = this;
		DeskPRO_Window.getMessageBroker().addMessageListener('kb.pending_article_removed', function(data) {
			self.removeFromList(data.pending_article_id)
		});
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

		var self = this;
		$('.save-trigger', el).click(function() {
			self.addDlg.closeOverlay();
			self.saveNewPendingArticle();
		});

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
			success: function(info) {
				var counter = $('.counter-pending', this.wrapper);
				var count = parseInt(counter.html());
				counter.html(count+1);

				$('.pending-articles-wrap', this.wrapper).show();

				var addEl = $(info.row_html);
				this.initRoutesOnCollection($('.with-route', addEl));
				$('tbody.pending-articles-list', this.wrapper).prepend(addEl);
			}
		});
	},

	removeFromList: function(id) {
		var counter = $('.counter-pending', this.wrapper);
		var count = parseInt(counter.html());
		counter.html(count-1);

		var wrap = $('.pending-articles-wrap', this.wrapper);
		$('tr.pending_article-' + id, wrap).remove();
		if (!$('tr.pending_article', wrap).length) {
			wrap.hide();
		}
	}
});