Orb.createNamespace('DeskPRO.Agent.PageFragment.ListPane');

DeskPRO.Agent.PageFragment.ListPane.KbList = new Class({
	Extends: DeskPRO.Agent.PageFragment.ListPane.Basic,

	wrapper: null,

	initPage: function(el) {
		var self = this;
		this.wrapper = el;

		this.displayOptions = new DeskPRO.Agent.PageHelper.DisplayOptions(this, {
			prefId: 'kb-filter',
			resultId: this.meta.resultId,
			refreshUrl: this.meta.refreshUrl
		});

		this.selectionBar = new DeskPRO.Agent.PageHelper.SelectionBar(this, {});

		this.initRoutesOnCollection($('.with-route', el));

		this.listWrapper = $('section.kb-simple-list', this.wrapper);

		DeskPRO_Window.getTabWatcher().addTabTypeWatcher('ticket', this);
		this.addEvent('watchedTabActivated', function(tab) {
			self.initVisibleTicket();
		});
		this.addEvent('watchedTabDeactivated', function(tab) {
			self.removeVisibleTicket();
		});

		// Or if we're already viewing a tab ticket...c
		if (DeskPRO_Window.getTabWatcher().isTabTypeActive('ticket')) {
			self.initVisibleTicket();
		}

		$('section.kb-simple-list', this.wrapper)
			.delegate('button.kb-insert-link', 'click', function() { self.insertIntoTicket($(this).data('article-id'), 'link') })
			.delegate('button.kb-insert-content', 'click', function() { self.insertIntoTicket($(this).data('article-id'), 'content') })

		this.relatedContentList = new DeskPRO.Agent.PageHelper.RelatedContentList(this, {
			contentListEl: this.listWrapper
		});
	},

	initVisibleTicket: function() {
		this.listWrapper.addClass('with-visible-ticket');
	},

	removeVisibleTicket: function() {
		this.listWrapper.removeClass('with-visible-ticket');
	},

	insertIntoTicket: function(article_id, action) {

		var ticketTab = DeskPRO_Window.getTabWatcher().getActiveTabIfType('ticket');
		if (!ticketTab) {
			return;
		}

		var ticketPage = ticketTab.page;

		$.ajax({
			url: BASE_URL + 'agent/kb/article/'+article_id+'/info',
			type: 'GET',
			dataType: 'json',
			success: function(data) {
				if (action == 'content') {
					ticketPage.appendToMessage(data.content);
				} else {
					ticketPage.appendToMessage(data.permalink);
				}
			}
		});
	}
});
