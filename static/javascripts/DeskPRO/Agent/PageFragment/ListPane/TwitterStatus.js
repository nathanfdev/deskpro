Orb.createNamespace('DeskPRO.Agent.PageFragment.ListPane');

DeskPRO.Agent.PageFragment.ListPane.TwitterStatus = new Orb.Class({
	Extends: DeskPRO.Agent.PageFragment.ListPane.Basic,

	initPage: function(el) {
		this.wrapper = $(el);

		this.header = $('.header', this.wrapper);
		this.content = $('.content', this.wrapper);

		this.note = $('.form-note', this.wrapper);
		this.reply = $('.form-reply', this.wrapper);

		this._initHeader();
		this._initContent();
		this._initControls();
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
		this._initUserPageLinks();
		this._initTimeago();
	},

	_initControls: function() {
		this._initFollow();
		this._initUnfollow();
		this._initAddNote();
		this._initAssign();
		this._initRetweet();
		this._initReply();
		this._initArchive();
	},

	_initSortByFields: function() {
		$('.order-by-menu a', this.header).on('change', $.proxy(this.reload, this));
	},

	_initIncludeFields: function() {
		$('.list-control-bar input:checkbox', this.header).on('change', $.proxy(this.reload, this));

		$('.list-control-bar label', this.header).each($.proxy(function(idx, el) {
			var label = $(el),
				input = $('.list-control-bar input[name='+label.data('for')+']', this.header),
				id = Orb.getUniqueId('twitter_options_'+label.data('for'));

			input.attr('id', id);
			label.attr('for', id);
		}, this));
	},

	_initUserPageLinks: function() {
		$('.photo', this.content).on('click', function() {
			DeskPRO_Window.runPageRouteFromElement(this);
			return false;
		});

		$('.user', this.content).on('click', function() {
			DeskPRO_Window.runPageRouteFromElement(this);
			return false;
		});
	},

	_initTimeago: function() {
		//this.initTimesOnCollection($('.timeago', this.content));
	},

	_getDisplayOptions: function() {
		var options = {
			sortbydate: $('.list-control-bar select[name=sortbydate] option:selected', this.header).val(),
			include: {}
		};

		$('.list-control-bar input:checkbox', this.header).each(function() {
			var field = $(this);
			options.include[field.attr('name')] = field.attr('checked') ? 1 : 0;
		});

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
		$('.status', this.content).removeClass('highlight');
		$('.status-'+id, this.content).addClass('highlight');
	},

	downlightStatus: function(id) {
		$('.status-'+id, this.content).removeClass('highlight');
	},

	_initFollow: function() {
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
	},

	_initAddNote: function() {
		var buttons = $('.controls .status-note', this.content);

		buttons.on('click', $.proxy(function(e) {
			if ($('.form-note', $(e.target).parents('.status')).length) {
				return false;
			}

			var status = $(e.target).parents('.status').attr('data-status-id'),
				note = this.note.clone(),
				area = $('textarea[name=text]', note);


			$(e.target).parents('.controls-row').find('.forms').append(note);
			this.highlightStatus(status);

			// close on ESCAPE
			var closeOnEscape = $.proxy(function(e) {
				if (e.which != 27) {
					return true;
				}

				this.downlightStatus(status);

				note.remove();

				// only once
				$(document).unbind('keydown', closeOnEscape);

				return true;
			}, this);
			$(document).on('keydown', closeOnEscape);

			// submit on ENTER
			area.on('keypress', $.proxy(function(e) {
				if (e.which != 13) {
					return true;
				}

				var text = area.val();
				note.remove();

				this.doAddNote(status, text);

				e.preventDefault();
				return false;
			}, this));

			note.show();
			area.focus();

			e.preventDefault();
			return false;
		}, this));
	},

	doAddNote: function(id, text) {
		$.ajax({
			url: this.getMetaData('saveNoteUrl'),
			dataType: 'json',
			data: {
				status_id: id,
				text: text
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

	_initAssign: function() {
		var buttons = $('.controls .status-assign', this.content);

		buttons.on('click', $.proxy(function(e) {
			e.preventDefault();
			alert("Todo");
			return false;
		}, this));
	},

	_initRetweet: function() {
		var buttons = $('.controls .status-retweet', this.content);

		buttons.on('click', $.proxy(function(e) {
			this.doRetweet($(e.target).parents('.status').attr('data-status-id'));

			e.preventDefault();
			return false;
		}, this));
	},

	doRetweet: function(id) {
		$.ajax({
			url: this.getMetaData('saveRetweetUrl'),
			dataType: 'json',
			data: {
				status_id: id,
				account_id: this.getMetaData('accountId')
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

	_initReply: function() {
		var buttons = $('.controls .status-reply', this.content);

		buttons.on('click', $.proxy(function(e) {
			if ($('.form-reply', $(e.target).parents('.status')).length) {
				return false;
			}

			var status = $(e.target).parents('.status').attr('data-status-id'),
				reply = this.reply.clone(),
				area = $('textarea[name=text]', reply);

			$(e.target).parents('.controls-row').find('.forms').append(reply);
			this.highlightStatus(status);

			// close on ESCAPE
			var closeOnEscape = $.proxy(function(e) {
				if (e.which != 27) {
					return true;
				}

				this.downlightStatus(status);

				reply.remove();

				// only once
				$(document).unbind('keydown', closeOnEscape);

				return true;
			}, this);
			$(document).on('keydown', closeOnEscape);

			// submit on ENTER
			reply.on('keypress', $.proxy(function(e) {
				if (e.which != 13) {
					return true;
				}

				var text = area.val(),
					type = $('input[type=radio][name=type]:checked', reply).val(),
					account_id = $('select[name=account] option:selected', reply).val();

				reply.remove();

				this.doReply(status, text, type, account_id);

				e.preventDefault();
				return false;
			}, this));

			reply.on('keyup', $.proxy(function(e) {
				// Update character count display
				var standardStatusLimit = this.getMetaData('standardStatusLimit') || 160;
				var statusLength = area.val().length;
				if (statusLength <= standardStatusLimit) {
					var html = '<span>' + statusLength + '</span> characters';
					$('.twitter-status-count span').html(html);
				}
				else {
					var html = 'Reply too long for twitter, will be sent as a long message';
					$('.twitter-status-count span').html(html);
				}
				e.preventDefault();
				return false;
			}, this));

			reply.show();
			area.focus();

			e.preventDefault();
			return false;
		}, this));

	},

	doReply: function(id, text, type, account_id) {
		$.ajax({
			url: this.getMetaData('saveReplyUrl'),
			dataType: 'json',
			data: {
				status_id: id,
				account_id: this.getMetaData('accountId'),
				text: text,
				type: type
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

	_initArchive: function() {
		var buttons = $('.controls .status-archive', this.content);

		buttons.on('click', $.proxy(function(e) {
			// $.data('status-id') results in math(status-id - 4) so use .attr()
			this.doArchive($(e.target).parents('.status').attr('data-status-id'));

			e.preventDefault();
			return false;
		}, this));
	},

	doArchive: function(id) {
		$.ajax({
			url: this.getMetaData('saveArchiveUrl'),
			dataType: 'json',
			data: { status_id: id },
			context: this,
			success: function(json) {
				if (json.success) {
					this.reload();
				} else {
					alert(json.error);
				}
			}
		})
	}
});
