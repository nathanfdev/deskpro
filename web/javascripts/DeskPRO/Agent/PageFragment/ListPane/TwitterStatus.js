Orb.createNamespace('DeskPRO.Agent.PageFragment.ListPane');

DeskPRO.Agent.PageFragment.ListPane.TwitterStatus = new Orb.Class({
	Extends: DeskPRO.Agent.PageFragment.ListPane.Basic,

	initializeProperties: function() {
		this.parent();
		this.TYPENAME = 'twitter-status-list';
		this.typeMap = {
			direct: 'agent_twitter_messages_list',
			reply: 'agent_twitter_replies_list',
			mention: 'agent_twitter_mentions_list',
			retweet: 'agent_twitter_retweets_list',
			timeline: 'agent_twitter_timeline_list',
			sent: 'agent_twitter_outgoing_list'
		};
		this.countReflected = {};
	},

	initPage: function(el) {
		this.wrapper = $(el);
		var self = this;

		this.meta.fetchResultsUrl = this.meta.statusListUrl;

		this.header = $('.header', this.wrapper);
		this.content = $('.content', this.wrapper);

		this._initHeader();
		this._initContent(this.content);
		this._initControls(this.content);

		var opt = {
			perPage: this.meta.perPage || 25,
			currentPage: this.meta.currentPage,
			totalCount: this.meta.totalCount,
			resultRowSelector: 'article.twitter-status',
			resultsContainer: this.content,
			preFetchCallback: function(data) {
				$.each(self._getDisplayOptions(), function(k, v) {
					if (/boolean|number|string/.test(typeof v)) {
						data.push({name: k, value: v});
					} else {
						$.each(v, function(kk, vv) {
							data.push({name: k + '[' + kk + ']', value: vv});
						});
					}
				});
				return data;
			},
			onPostSetNewResults: function() {
				self._afterLoading();
			}
		};
		this.resultsHelper = new DeskPRO.Agent.PageHelper.Results(this, opt);
		this.ownObject(this.resultsHelper);

		this.twitterHelper = new DeskPRO.Agent.PageHelper.Twitter(this.content, this, {
			statusArchiveHideCallback: function(row) {
				var id = parseInt(row.data('status-id'), 10);
				if (!self.countReflected[id]) {
					self.countReflected[id] = true;
					self.resultsHelper.adjustResultCount(-1);
				}
				self._afterTweetRemoved(200);
			}
		});

		DeskPRO_Window.getMessageBroker().addMessageListener('agent.tweet-added', function (data) {
			self.adjustTweetCountsFromClientMessage(data, 1);
			self.adjustShownTweetsForTweetAdded(data);

			self.countReflected = {};
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

			self.adjustShownTweetsForTweetUpdated(data);

			self.countReflected = {};
		});
	},

	adjustTweetCountsFromClientMessage: function(data, adjustAmount) {
		var accountId = data.account_id;

		if (this.countReflected[data.account_status_id]) {
			return;
		}

		if (this._tweetAppliesToPage(data) && this.resultsHelper && this.resultsHelper.options) {
			this.countReflected[data.account_status_id] = true;
			this.resultsHelper.adjustResultCount(adjustAmount);

			if (this.meta.listRoute == this.typeMap.timeline && this.resultsHelper.resultCount > 1000) {
				this.resultsHelper.setResultCount(1000);
			}
		}
	},

	adjustShownTweetsForTweetAdded: function(data) {
		if (this.content.find('.row-item.status-' + data.account_status_id).length) {
			// tweet already shown
			return;
		}

		if (this._tweetAppliesToPage(data)) {
			this.addTweetToPage(data.account_status_id, data.tweet_html);
		}
	},

	_tweetAppliesToPage: function(data) {
		if (this.meta.accountId && this.meta.accountId != data.account_id) {
			return false;
		}

		if (this.typeMap[data.status_type] && this.meta.listRoute == this.typeMap[data.status_type]) {
			return true;
		}

		switch (data.status_type) {
			case 'reply':
			case 'mention':
			case 'retweet':
			case 'direct':
				if (this.meta.listRoute == 'agent_twitter_inbox_list') {
					return true;
				}
		}

		if (data.is_from_self) {
			if (this.meta.listRoute == this.typeMap.sent) {
				return true;
			} else if (this.menuOptions.filter('[name=account]').is(':checked')) {
				return true;
			}
		}

		return false;
	},

	adjustShownTweetsForTweetUpdated: function(data) {
		if (this.content.find('.row-item.status-' + data.account_status_id).length) {
			if (data.change_archived) {
				var showArchived = this.menuOptions.filter('[name=archived]').is(':checked');
				if (data.is_archived && !showArchived) {
					this.removeTweetFromPage(data.account_status_id);
				} else if (!data.is_archived) {
					this.addTweetToPage(data.account_status_id, data.tweet_html);
				}
			}
			if (data.deleted) {
				this.removeTweetFromPage(data.account_status_id);
			}
			if (data.reply_added_html) {

			}
			if (data.note_added_html) {

			}
			if (data.note_deleted_id) {

			}
		} else {
			if (data.change_archived && !data.is_archived) {
				this.addTweetToPage(data.account_status_id, data.tweet_html);
			}
		}

		if (this.content.find('.twitter-reply-' + data.account_status_id).length) {
			if (data.deleted) {
				this.removeReplyFromPage(data.account_status_id);
			}
		}
	},

	addTweetToPage: function(account_status_id, html) {
		// todo: remove last one from page if showing too many

		if (!this.resultsHelper || !this.resultsHelper.options) {
			// page destroyed
			return;
		}

		if (!this.countReflected[account_status_id]) {
			this.resultsHelper.adjustResultCount(1);
			this.countReflected[account_status_id] = true;
		}

		var $html = $(html);
		this.content.find('.twitter-status-list').prepend($html);
		this._afterLoading($html);
	},

	removeTweetFromPage: function(account_status_id) {
		var el = this.content.find('.row-item.status-' + account_status_id);
		if (el.length) {
			el.remove();
			if (!this.countReflected[account_status_id]) {
				this.resultsHelper.adjustResultCount(-1);
				this.countReflected[account_status_id] = true;
			}
			this._afterTweetRemoved(0);
		}
	},

	removeReplyFromPage: function(account_status_id) {
		var el = this.content.find('.twitter-reply-' + account_status_id);
		if (el.length) {
			var row = this.twitterHelper.closestRow(el);
			el.remove();
			if (!row.find('.twitter-replies .twitter-reply').length) {
				row.find('.reply-list').hide();
			}
		}
	},

	_afterTweetRemoved: function(delay) {
		var pageHelper = this.resultsHelper,
			page = pageHelper.getCurrentPage(),
			numPages = pageHelper.getNumPages();
		var self = this;

		if (!this.resultsHelper || !this.resultsHelper.options) {
			// page destroyed
			return;
		}

		pageHelper.updateShowingCount();

		if (page < numPages) {
			var data = this._getDisplayOptions();
			data.last = 1;
			data.page = page;

			setTimeout(function() {
				$.ajax({
					url: this.getMetaData('statusListUrl'),
					dataType: 'html',
					data: data,
					success: function(html) {
						var $html = $(html);
						self.content.find('.twitter-status-list').append($html);
						self._afterLoading($html);
					}
				});
			}, delay || 0);
		} else if (pageHelper.resultCount <= 0) {
			this.wrapper.find('.list-listing.no-results').show();
			this.wrapper.find('.results-nav').hide();
		}
	},

	_afterLoading: function(content) {
		if (!content) { content = this.content; }
		this._initContent(content);
		this._initControls(content);

		if (this.selectionBar) {
			this.selectionBar.updateCount();
		}
		if (this.resultsHelper && this.resultsHelper.options) {
			this.resultsHelper.updateShowingCount();
		}
	},

	_initHeader: function() {
		this._initSortByFields();
		this._initIncludeFields();

		var self = this;

		this.selectionBar = new DeskPRO.Agent.PageHelper.SelectionBar(this, {
			onButtonClick: function() {
				self.massActions.open();
			},
			checkSelector: '.twitter-status:not(.archived) input.item-select'
		});
		this.ownObject(this.selectionBar);

		this.massActions = new DeskPRO.Agent.PageHelper.MassActions(this, {
			isListView: false,
			applyAction: function(wrapper, formData) {
				var data = formData,
					myFormData = $('input, textarea, select', wrapper).serializeArray();

				$(myFormData).each(function(index, param) {
					data[param.name] = param.value;
				});

				wrapper.addClass('loading');

				$.ajax({
					type: 'POST',
					url: BASE_URL + "agent/twitter/status/ajax-mass-save.json",
					'data': data,
					'dataType': 'json',
					success: function() {
						self.massActions.close();
						self.reload();
					}
				}).done(function() {
					wrapper.removeClass('loading');
				});
			},
			closeOnApply: false,
			openAction: function(wrapper) {
				if (!wrapper.data('twitter-helper')) {
					wrapper.data('twitter-helper',
						new DeskPRO.Agent.PageHelper.Twitter($('#twitter-mass-action-overlay'), self)
					);
				}
			}
		});
		this.ownObject(this.massActions);
	},

	_initContent: function(content) {
		$('.timeago', content).timeago();
		content.find('textarea').TextAreaExpander();

		var list = content.find('.twitter-status-list');
		if (list.length && list.data('page') && this.resultsHelper) {
			this.resultsHelper.setPage(parseInt(list.data('page'), 10), true);
			this.resultsHelper.setResultCount(parseInt(list.data('total-count'), 10));
		}
	},

	_initControls: function(content) {
		var self = this;

		content.find('li.opt-trigger.agent select').not('.has-init').each(function() {
			var row = $(this).closest('article.twitter-status');
			DP.select($(this));

			$(this).on('change', function() {
				var val = $(this).val();
				var label = $(this).find(':selected').text().trim();

				if (val == 'agent:' + DESKPRO_PERSON_ID) {
					label = 'Me';
				}

				row.find('li.opt-trigger.agent label').text(label);

				var id = $(this).closest('.twitter-status').attr('data-status-id');

				$.ajax({
					url: self.getMetaData('saveAssignUrl'),
					type: 'POST',
					dataType: 'json',
					data: { account_status_id: id, assign: val },
					success: function(json) {
						if (json.error) {
							alert(json.error);
						}
					}
				});
			});
		});
	},

	_initSortByFields: function() {
		var self = this;

		var sortMenuBtn = $('.order-by-menu-trigger', this.header).first();
		this.sortingMenu = new DeskPRO.UI.Menu({
			triggerElement: sortMenuBtn,
			menuElement: $('.order-by-menu', this.header).first(),
			onItemClicked: function(info) {
				var item = $(info.itemEl);

				var prop = item.data('order-by');
				var label = item.find('.label').text().trim();

				// Change the displayed label for some visual feedback
				$('.label label', sortMenuBtn).text(label);
				sortMenuBtn.find('.order-dir').hide();
				sortMenuBtn.find('.order-dir.' + prop.split('_').pop()).show();

				sortMenuBtn.data('dir', prop);

				self.sortingMenu.close();
				self.reload();
			}
		});
		this.ownObject(this.sortingMenu);
	},

	_initIncludeFields: function() {
		var self = this;

		this.menuOptions = this.header.find('.btn-controls input:checkbox');
		this.menuOptions.click(function() { self.reload(); });
	},

	_getDisplayOptions: function() {
		var options = {
			include: {}
		};

		if (this.menuOptions) {
			this.menuOptions.each(function() {
				var field = $(this);
				options.include[field.attr('name')] = field.attr('checked') ? 1 : 0;
			});
		}

		return options;
	},

	reload: function() {
		$.ajax({
			url: this.getMetaData('statusListUrl'),
			dataType: 'html',
			data: this._getDisplayOptions(),
			context: this,
			success: function(html) {
				this.content.html(html);
				this._afterLoading();
			}
		});
	},

	highlightStatus: function(id) {
		$('.twitter-status', this.content).removeClass('highlight');
		$('.status-'+id, this.content).addClass('highlight');
	},

	downlightStatus: function(id) {
		$('.twitter-status-'+id, this.content).removeClass('highlight');
	}
});
