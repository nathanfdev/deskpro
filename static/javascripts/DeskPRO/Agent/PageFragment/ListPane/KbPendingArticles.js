Orb.createNamespace('DeskPRO.Agent.PageFragment.ListPane');

DeskPRO.Agent.PageFragment.ListPane.KbPendingArticles = new Class({
	Extends: DeskPRO.Agent.PageFragment.ListPane.Basic,

	wrapper: null,

	initPage: function(el) {
		var self = this;
		this.wrapper = el;

		this.initRoutesOnCollection($('.with-route', el));

		this.selectionBar = new DeskPRO.Agent.PageHelper.SelectionBar(this, {});
		this.actionsMenu = new DeskPRO.UI.Menu({
			triggerElement: $('button.perform-actions-trigger:first', this.wrapper),
			menuElement: $('ul.actions-menu:first', this.wrapper),
			onItemClicked: function(info) {
				var ids = self.selectionBar.getCheckedValues();
				var els = [];

				var formData = [];
				Array.each(ids, function(id) {
					formData.push({
						name: 'ids[]',
						value: id
					});

					els.push($('article.pending-article-' + id + ':first', self.wrapper).get(0));
				});

				$(els).fadeOut();

				var action = $(info.itemEl).data('action');

				$.ajax({
					url: BASE_URL + 'agent/kb/pending-articles/mass-actions/' + action,
					data: formData,
					type: 'POST',
					dataType: 'json',
					error: function() {
						$(els).show();
					},
					success: function() {
						$(els).remove();
						DeskPRO_Window.util.modCountEl('#kb_pending_count', '-', els.length);
					}
				});
			}
		});

		DeskPRO_Window.getMessageBroker().addMessageListener('kb.pending_article_removed', function(data) {
			self.removeFromList(data.pending_article_id)
		});

		$('.add-new-trigger', this.wrapper).click(function() {
			var formWrap = $('.add-new-form', self.wrapper);
			if (formWrap.is(':visible')) {
				formWrap.slideUp();
			} else {
				formWrap.slideDown();
			}
		});

		$('.save-new-trigger', this.wrapper).click(this.saveNewPendingArticle.bind(this));

		$('section.pending-articles-list', this.wrapper).delegate('.pending-delete', 'click', function(ev) {
			ev.stopPropagation();
			var row = $(this);
			var x = 0;
			while (!row.is('article')) {
				if (x++ > 10) return;
				row = row.parent();
			}

			row.slideUp('fast');

			var id = $('input.item-select', row).val();

			$.ajax({
				url: BASE_URL + 'agent/kb/pending-articles/' + id + '/remove',
				type: 'POST',
				dataType: 'json',
				error: function() {
					row.show();
				},
				success: function() {
					row.remove();
					DeskPRO_Window.util.modCountEl('#kb_pending_count', '-');
				}
			});
		});

		$('section.pending-articles-list', this.wrapper).delegate('.pending-create', 'click', function(ev) {
			ev.stopPropagation();
			var row = $(this);
			var x = 0;
			while (!row.is('article')) {
				if (x++ > 10) return;
				row = row.parent();
			}

			var id = $('input.item-select', row).val();
			var ticketRoute = $('input.item-select', row).data('ticket-route');

			$.ajax({
				url: BASE_URL + 'agent/kb/pending-articles/' + id + '/info',
				type: 'POST',
				dataType: 'json',
				success: function(data) {
					if (ticketRoute) {
						DeskPRO_Window.runPageRoute(ticketRoute);
					}

					DeskPRO_Window.newArticleLoader.open(function(page) {
						if (data.ticket_subject) {
							page.setTitle(data.ticket_subject);
						}
						if (data.message_content_html) {
							page.setContent(data.message_content_html, true);
						}
						page.setPendingArticleId(id);
					});
				}
			});
		});
	},

	saveNewPendingArticle: function() {
		var formWrap = $('.add-new-form', this.wrapper);
		var val = $('.add-new-form textarea', this.wrapper).val().trim();

		if (!val) {
			formWrap.slideUp();
			return;
		}

		var data = [];
		data.push({
			name: 'comment',
			value: val
		});

		$.ajax({
			url: BASE_URL + 'agent/kb/pending-articles/new',
			type: 'POST',
			data: data,
			context: this,
			dataType: 'json',
			success: function(info) {

				$('textarea:first', formWrap).val('');
				formWrap.slideUp();

				var addEl = $(info.row_html);
				this.initRoutesOnCollection($('.with-route', addEl));
				$('section.pending-articles-list', this.wrapper).prepend(addEl);

				DeskPRO_Window.util.modCountEl('#kb_pending_count', '+');
			}
		});
	},

	removeFromList: function(id) {
		$('article.pending-article-' + id, this.wrapper).slideUp('fast');
		DeskPRO_Window.util.modCountEl('#kb_pending_count', '-');
	}
});
