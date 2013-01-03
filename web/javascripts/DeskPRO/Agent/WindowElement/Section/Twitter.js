Orb.createNamespace('DeskPRO.Agent.WindowElement.Section');

DeskPRO.Agent.WindowElement.Section.Twitter = new Orb.Class({
	Extends: DeskPRO.Agent.WindowElement.Section.AbstractSection,

	init: function() {
		this.buttonEl = $('#twitter_section');
		this.urlFragmentName = 'twitter';
		var self = this;

		this.setSectionElement($('<section id="twitter_outline"></section>'));

		this.refresh();

		DeskPRO_Window.getMessageBroker().addMessageListener('agent.tweet-added', function (data) {
			self.adjustTweetCountsFromClientMessage(data, 1);
		});

		DeskPRO_Window.getMessageBroker().addMessageListener('agent.tweet-updated', function (data) {
			if (data.change_archived) {
				if (data.is_archived) {
					// moved to archived, reduce counts
					self.adjustTweetCountsFromClientMessage(data, -1);
				} else {
					// moved to unarchived, increase counts
					self.adjustTweetCountsFromClientMessage(data, 1);
				}
			} else if (data.deleted) {
				self.adjustTweetCountsFromClientMessage(data, -1);
			}
		});
	},

	adjustTweetCountsFromClientMessage: function(data, adjustAmount) {
		var accountId = data.account_id;

		switch (data.status_type) {
			case 'timeline':
				var el = this.getSectionElement().find('#twitter-section-counts-' + accountId + ' .twitter-timeline-counter');
				var count = parseInt(el.text().trim(), 10);
				count += adjustAmount;
				if (count > 1000) {
					count = 1000;
				} else if (count < 0) {
					count = 0;
				}
				el.text(count);
				break;

			case 'sent':
				var el = this.getSectionElement().find('#twitter-section-counts-' + accountId + ' .twitter-sent-counter');
				var count = parseInt(el.text().trim(), 10) + adjustAmount;
				if (count < 0) {
					count = 0;
				}
				el.text(count);
				break;

			case 'direct':
				if (data.is_from_self) {
					// own DM, consider as sent
					break;
				}
				// break missing intentionally

			case 'reply':
			case 'mention':
			case 'retweet':
				var el = this.getSectionElement().find('#twitter-section-counts-' + accountId + ' .twitter-' + data.status_type + '-counter');
				var count = parseInt(el.text().trim(), 10) + adjustAmount;
				if (count < 0) {
					count = 0;
				}
				el.text(count);

				var inbox = this.getSectionElement().find('#twitter-section-counts-' + accountId + ' .twitter-inbox-counter');
				var count = parseInt(inbox.text().trim(), 10) + adjustAmount;
				if (count < 0) {
					count = 0;
				}
				inbox.text(count);

				this.recountBadge();
				break;
		}
	},

	refresh: function() {
		DeskPRO_Window.getSectionData('twitter_section', this._initSection.bind(this));
	},

	_initSection: function(data) {
		this.setHasInitialLoaded();
		this.contentEl.html(data.section_html);

		this.getSectionElement().on('click', '.twitter-account-add-status', function() {
			if (DeskPRO_Window.newTweetLoader) {
				var accountId = $(this).data('account-id');
				DeskPRO_Window.newTweetLoader.open(function(page) {
					var select = page.getEl('from_account');
					if (select.length && select.is('.with-select2')) {
						select.select2('val', [accountId]);
					}
				});
			}
		});

		this.getSectionElement().on('click', '.sub-toggle', function(ev) {
			var row = $(this).closest('li');
			var sub = $('> ul.sub-group', row);
			if (sub.length) {
				if (sub.is(':visible')) {
					row.removeClass('sub-expanded');
					sub.slideUp('fast');
				} else {
					row.addClass('sub-expanded');
					sub.slideDown('fast');
				}
			}
		});

		this.recountBadge();
	},

	recountBadge: function() {
		var count = 0;
		this.contentEl.find('.twitter-inbox-counter').each(function() {
			count += parseInt($(this).text().trim(), 10) || 0;
		});
		this.updateBadge(count);
	}
});
