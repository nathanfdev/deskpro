Orb.createNamespace('DeskPRO.Agent.PageFragment.ListPane');

DeskPRO.Agent.PageFragment.ListPane.TwitterStatus = new Orb.Class({
	Extends: DeskPRO.Agent.PageFragment.ListPane.Basic,

	initializeProperties: function() {
		this.parent();
		this.TYPENAME = 'twitter-status-list';
	},

	initPage: function(el) {
		this.wrapper = $(el);

		this.header = $('.header', this.wrapper);
		this.content = $('.content', this.wrapper);

		this._initHeader();
		this._initContent();
		this._initControls();

		var self = this;

		// user links
		this.content.on('click', '.photo, .user', function() {
			DeskPRO_Window.runPageRouteFromElement(this);
			return false;
		});
		this.content.on('click', '.mention', function() {
			var route = 'page:' + BASE_URL + 'agent/twitter/user/' + $(this).data('user-id');
			DeskPRO_Window.runPageRoute(route);
			return false;
		});

		// status favorite/unfavorite
		this.content.on('click', '.add-favorite', function(e) {
			e.preventDefault();

			$(this).addClass('favorited').removeClass('add-favorite');

			var id = $(this).closest('.twitter-status').attr('data-status-id');
			self.doFavorite(id, 1);
		});
		this.content.on('click', '.favorited', function(e) {
			e.preventDefault();

			$(this).addClass('add-favorite').removeClass('favorited');

			var id = $(this).closest('.twitter-status').attr('data-status-id');
			self.doFavorite(id, 0);
		});

		// status archive/unarchive
		this.content.on('click', '.status-archive', function(e) {
			e.preventDefault();

			var row = $(this).closest('.twitter-status');

			$(this).addClass('status-archived').removeClass('status-archive');
			row.addClass('archived');

			var id = row.attr('data-status-id');
			self.doArchive(id, 1);

			if (self.menuOptions && !self.menuOptions.filter('[name=archived]').is(':checked')) {
				row.remove();
			}
		});
		this.content.on('click', '.status-archived', function(e) {
			e.preventDefault();

			$(this).addClass('status-archive').removeClass('status-archived');
			$(this).closest('.twitter-status').removeClass('archived');

			var id = $(this).closest('.twitter-status').attr('data-status-id');
			self.doArchive(id, 0);
		});


		// retweet/unretweet trigger
		this.content.on('click', 'li.opt-trigger.retweet', function(e) {
			e.preventDefault();

			var link = $(this);

			var id = link.closest('.twitter-status').attr('data-status-id');

			if (confirm('Are you sure you want to retweet this?')) {
				$.ajax({
					url: self.getMetaData('saveRetweetUrl'),
					type: 'POST',
					dataType: 'json',
					data: {
						account_status_id: id
					},
					success: function(json) {
						if (json.success) {
							link.addClass('retweeted').removeClass('retweet');
							link.find('label').text('Retweeted');
						} else {
							alert(json.error);
						}
					}
				});
			}
		});
		this.content.on('click', 'li.opt-trigger.retweeted', function(e) {
			e.preventDefault();

			var link = $(this);

			var id = link.closest('.twitter-status').attr('data-status-id');

			if (confirm('Are you sure you want to un-retweet this?')) {
				$.ajax({
					url: self.getMetaData('saveUnretweetUrl'),
					type: 'POST',
					dataType: 'json',
					data: {
						account_status_id: id
					},
					success: function(json) {
						if (json.success) {
							link.addClass('retweet').removeClass('retweeted');
							link.find('label').text('Retweet');
						} else {
							alert(json.error);
						}
					}
				});
			}
		});

		// reply triggers
		this.content.on('click', 'li.opt-trigger.reply', function(e) {
			e.preventDefault();

			var row = $(this).closest('.twitter-status');

			var newReply = row.find('.new-reply');
			if (newReply.is(':visible')) {
				newReply.hide();
			} else {
				newReply.show();

				var textarea = newReply.find('textarea');
				if (!$.trim(textarea.val()).length && !row.hasClass('dm')) {
					var name = row.find('.title .screen-name').text();

					textarea.val(name + ' ');
				}

				textarea.focus();
			}
		});
		this.content.on('click', '.new-reply .reply-type li', function() {
			var $this = $(this);
			var replyContainer = $this.closest('.new-reply');

			replyContainer.find('.reply-type li').removeClass('on');
			$this.addClass('on');
			replyContainer.find('.reply-type-hidden').val($this.data('type'));
		});
		this.content.on('click', '.cancel-reply-trigger', function() {
			var replyContainer = $(this).closest('.new-reply');
			replyContainer.hide();
		});
		this.content.on('click', '.save-reply-trigger', function(e) {
			e.preventDefault();

			var row = $(this).closest('.twitter-status');
			var id = row.attr('data-status-id');
			var replyContainer = $(this).closest('.new-reply');

			var val = $.trim(replyContainer.find('textarea').val());
			if (!val.length) {
				replyContainer.hide();
				return;
			}

			var type = replyContainer.find('.reply-type-hidden').val();

			replyContainer.addClass('loading');

			$.ajax({
				url: self.getMetaData('saveReplyUrl'),
				type: 'POST',
				dataType: 'json',
				data: {
					account_status_id: id,
					text: val,
					type: type
				},
				success: function(json) {
					if (json.success) {
						if (json.html) {
							var html = $(json.html);
							row.find('.twitter-replies').append(html);
							$('.timeago', html).timeago();

							row.find('.reply-list').show();
						}

						replyContainer.hide();
						replyContainer.find('textarea').val('')
					} else {
						alert(json.error);
					}
				}
			}).always(function() {
				replyContainer.removeClass('loading');
			});
		});


		// note triggers
		this.content.on('click', '.note-btn', function() {
			var newNote = $(this).closest('.twitter-status').find('.new-note');
			if (newNote.is(':visible')) {
				newNote.hide();
			} else {
				newNote.show();
				newNote.find('textarea').focus();
			}
		});
		this.content.on('click', '.cancel-note-trigger', function() {
			var noteContainer = $(this).closest('.new-note');
			noteContainer.hide();
		});
		this.content.on('click', '.save-note-trigger', function(e) {
			e.preventDefault();

			var row = $(this).closest('.twitter-status');
			var id = row.attr('data-status-id');
			var noteContainer = $(this).closest('.new-note');

			var val = $.trim(noteContainer.find('textarea').val());
			if (!val.length) {
				noteContainer.hide();
				return;
			}

			noteContainer.addClass('loading');

			$.ajax({
				url: self.getMetaData('saveNoteUrl'),
				type: 'POST',
				dataType: 'json',
				data: {
					account_status_id: id,
					text: val
				},
				success: function(json) {
					if (json.success) {
						if (json.html) {
							var html = $(json.html);
							row.find('.note-list').append(html);
							$('.timeago', html).timeago();

							row.find('.notes-wrap').show();
						}

						noteContainer.hide();
						noteContainer.find('textarea').val('')
					} else {
						alert(json.error);
					}
				}
			}).always(function() {
				noteContainer.removeClass('loading');
			});
		});
	},

	_afterLoading: function() {
		this._initContent();
		this._initControls();
	},

	_initHeader: function() {
		//this._initSortByFields();
		this._initIncludeFields();
	},

	_initContent: function() {
		$('.timeago', this.content).timeago();
	},

	_initControls: function() {
		//this._initFollow();
		//this._initUnfollow();

		var self = this;

		this.content.find('li.opt-trigger.agent select').not('.has-init').each(function() {
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
		$('.order-by-menu a', this.header).on('change', $.proxy(this.reload, this));
	},

	_initIncludeFields: function() {
		var self = this;

		this.menuOptions = this.header.find('.display-options-menu input:checkbox');

		var timer = false;

		var optionsMenu = new DeskPRO.UI.Menu({
			triggerElement: this.header.find('.display-options-trigger'),
			menuElement: this.header.find('.display-options-menu'),
			onItemClicked: function(info) {
				// this can be called twice so use the timer to ensure only one run happens
				if (timer) {
					clearTimeout(timer);
				}
				timer = setTimeout(function() {
					self.reload();
				}, 0);
			}
		});
	},

	_getDisplayOptions: function() {
		var options = {
			//sortbydate: $('.list-control-bar select[name=sortbydate] option:selected', this.header).val(),
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
	},

	/*_initFollow: function() {
		var buttons = $('.follow a', this.content);

		buttons.on('click', $.proxy(function(e) {
			this.doFollow($(e.target).parents('.status').attr('data-user-id'));
		}, this));
	},

	doFollow: function(id) {
		$.ajax({
			url: this.getMetaData('saveFollowUrl'),
			dataType: 'json',
			data: {
				account_id: this.getMetaData('accountId'),
				user_id: id
			},
			context: this,
			success: function(json) {
				if (json.success) {
					this.reload();
				} else {
					alert(json.error);
				}
			}
		});
	},

	_initUnfollow: function() {
		var buttons = $('.unfollow a', this.content);

		buttons.on('click', $.proxy(function(e) {
			this.doUnfollow($(e.target).parents('.status').attr('data-user-id'));
		}, this));
	},

	doUnfollow: function(id) {
		$.ajax({
			url: this.getMetaData('saveUnfollowUrl'),
			dataType: 'json',
			data: {
				account_id: this.getMetaData('accountId'),
				user_id: id
			},
			context: this,
			success: function(json) {
				if (json.success) {
					this.reload();
				} else {
					alert(json.error);
				}
			}
		});
	},*/

	doArchive: function(id, archive) {
		$.ajax({
			url: this.getMetaData('saveArchiveUrl'),
			type: 'POST',
			dataType: 'json',
			data: { account_status_id: id, archive: archive ? 1 : 0 },
			success: function(json) {
				if (json.error) {
					alert(json.error);
				}
			}
		});
	},

	doFavorite: function(id, favorite) {
		$.ajax({
			url: this.getMetaData('saveFavoriteUrl'),
			type: 'POST',
			dataType: 'json',
			data: { account_status_id: id, favorite: favorite ? 1 : 0 },
			success: function(json) {
				if (json.error) {
					alert(json.error);
				}
			}
		});
	}
});
