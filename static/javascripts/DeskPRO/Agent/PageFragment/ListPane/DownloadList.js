Orb.createNamespace('DeskPRO.Agent.PageFragment.ListPane');

DeskPRO.Agent.PageFragment.ListPane.DownloadList = new Orb.Class({
	Extends: DeskPRO.Agent.PageFragment.ListPane.Basic,

	initPage: function(el) {
		var self = this;
		this.wrapper = el;

		this.displayOptions = new DeskPRO.Agent.PageHelper.DisplayOptions(this, {
			prefId: 'download-filter',
			resultId: this.meta.resultId,
			refreshUrl: this.meta.refreshUrl
		});
		this.ownObject(this.displayOptions);

		this.selectionBar = new DeskPRO.Agent.PageHelper.SelectionBar(this, {

		});
		this.ownObject(this.selectionBar);

		this.listWrapper = $('section.downloads-simple-list', this.wrapper)
			.on('click', 'button.dl-insert-link', function() { self.insertIntoTicket($(this).data('download-id'), 'link') })
			.on('click', 'button.dl-insert-attach', function() { self.insertIntoTicket($(this).data('download-id'), 'attach') });

		DeskPRO_Window.getTabWatcher().addTabTypeWatcher('ticket', this);
		this.addEvent('watchedTabActivated', function(tab) {
			self.initVisibleTicket();
		});
		this.addEvent('watchedTabDeactivated', function(tab) {
			self.removeVisibleTicket();
		});

		// Or if we're already viewing a tab ticket...
		if (DeskPRO_Window.getTabWatcher().isTabTypeActive('ticket')) {
			self.initVisibleTicket();
		}

		this.relatedContentList = new DeskPRO.Agent.PageHelper.RelatedContentList(this, {
			contentListEl: this.listWrapper
		});
		this.ownObject(this.relatedContentList);

		this.enableHighlightOpenRows('download', 'download_id', 'article.download-');
	},

	initVisibleTicket: function() {
		this.listWrapper.addClass('with-visible-ticket');
	},

	removeVisibleTicket: function() {
		this.listWrapper.removeClass('with-visible-ticket');
	},

	insertIntoTicket: function(download_id, action) {

		var ticketTab = DeskPRO_Window.getTabWatcher().getActiveTabIfType('ticket');
		if (!ticketTab) {
			return;
		}

		var ticketPage = ticketTab.page;

		$.ajax({
			url: BASE_URL + 'agent/downloads/file/'+download_id+'/info',
			type: 'GET',
			dataType: 'json',
			success: function(data) {
				if (action == 'attach') {
					ticketPage.addAttachToList(data);
				} else {
					ticketPage.appendToMessage(data.permalink);
				}
			}
		});
	}
});
