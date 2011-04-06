Orb.createNamespace('DeskPRO.Agent.PageFragment.ListPane');

DeskPRO.Agent.PageFragment.ListPane.TwitterStatus = new Class({
	Extends: DeskPRO.Agent.PageFragment.ListPane.Basic,

	wrapper: null,
	header: null,
	content: null,

	// note: null,
	// reply: null,

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
		this._initOrderBySelectField();
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

	_initOrderBySelectField: function() {
		$('.display-options select[name=sortbydate]', this.header)
			.change($.proxy(this.reload, this));
	},

	_initIncludeFields: function() {
		$('.display-options input:checkbox', this.header).change($.proxy(this.reload, this));

		$('.display-options label', this.header).each(function() {
			var label = $(this),
				input = $('.display-options input[name='+label.data('for')+']'),
				id = Orb.getUniqueId('twitter_options_'+label.data('for'));

			input.attr('id', id);
			label.attr('for', id);
		});
	},

	_initUserPageLinks: function() {
		$('.photo', this.content).click(function() {
			DeskPRO_Window.runPageRouteFromElement(this);
		});

		$('.user', this.content).click(function() {
			DeskPRO_Window.runPageRouteFromElement(this);
		});
	},

	_initTimeago: function() {
		this.initTimesOnCollection($('.timeago', this.content));
	},

	_getDisplayOptions: function() {
		var options = {
			sortbydate: $('.display-options select[name=sortbydate] option:selected', this.header).attr('name'),
			include: {}
		};

		$('.display-options input:checkbox', this.header).each(function() {
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
		console.log($('.status-'+id, this.content));
		$('.status', this.content).removeClass('highlight');
		$('.status-'+id, this.content).addClass('highlight');
	},

	downlightStatus: function(id) {
		$('.status-'+id, this.content).removeClass('highlight');
	},

	_initFollow: function() {
		var buttons = $('.follow a', this.content);

		buttons.click($.proxy(function(e) {
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

		buttons.click($.proxy(function(e) {
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
		var buttons = $('.controls .note a', this.content);

		buttons.click($.proxy(function(e) {
			var status = $(e.target).parents('.status').attr('data-status-id'),
				note = this.note.clone(),
				area = $('textarea[name=text]', note);

			$(e.target).parents('.status').append(note);
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
			$(document).keydown(closeOnEscape);

			// submit on ENTER
			area.keypress($.proxy(function(e) {
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
		var buttons = $('.controls .assign a', this.content);

		buttons.click($.proxy(function(e) {
			e.preventDefault();
			return false;
		}, this));
	},

	_initRetweet: function() {
		var buttons = $('.controls .retweet a', this.content);

		buttons.click($.proxy(function(e) {
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
		var buttons = $('.controls .reply a', this.content);

		buttons.click($.proxy(function(e) {
			var status = $(e.target).parents('.status').attr('data-status-id'),
				reply = this.reply.clone(),
				area = $('textarea[name=text]', reply);

			$(e.target).parents('.status').append(reply);
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
			$(document).keydown(closeOnEscape);

			// submit on ENTER
			reply.keypress($.proxy(function(e) {
				if (e.which != 13) {
					return true;
				}

				var text = area.val(),
					type = $('input[type=radio][name=type]:checked', reply).val(),
					account_id = $('input[type=radio][name="account"]:checked', reply).val();

				reply.remove();

				this.doReply(status, text, type, account_id);

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
				account_id: id,
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
		var buttons = $('.controls .archive a', this.content);

		buttons.click($.proxy(function(e) {
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
