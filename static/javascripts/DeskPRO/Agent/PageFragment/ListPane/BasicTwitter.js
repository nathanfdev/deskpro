Orb.createNamespace('DeskPRO.Agent.PageFragment.ListPane');

DeskPRO.Agent.PageFragment.ListPane.BasicTwitter = new Class({
	Extends: DeskPRO.Agent.PageFragment.ListPane.Basic,

	el: null,
	head: null,
	listing: null,

	initPage: function(el) {
		this.head    = $('.twitter-head', el);
		this.listing = $('.twitter-listing', el);

		this._initHead();
		this._initStatusControls();
	},

	_initHead: function() {

	},

	_initStatusControls: function() {
	},

	_initAddNote: function() {
		var buttons = $('li.tweet-item .note a', this.listing);

		buttons.click($.proxy(function(e) {
			var status = $(e.target).parents('li.tweet-item').attr('data-status-id'),
				note = this.note.clone(),
				area = $('textarea[name=text]', note);

			$(e.target).parents('li.tweet-item').append(note);

			// close on ESCAPE
			var closeOnEscape = function(e) {
				if (e.which != 27) {
					return true;
				}

				note.remove();

				// only once
				$(document).unbind('keydown', closeOnEscape);

				return true;
			};
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
				}

				// @TODO handle json.error
			}
		});
	},

	_initAssign: function() {
		var buttons = $('li.tweet-item .assign a', this.listing);

		buttons.click($.proxy(function(e) {
			e.preventDefault();
			return false;
		}, this));
	},

	_initRetweet: function() {
		var buttons = $('li.tweet-item .retweet a', this.listing);

		buttons.click($.proxy(function(e) {
			this.doRetweet($(e.target).parents('li.tweet-item').attr('data-status-id'));

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
				}

				// @TODO handle json.error
			}
		});
	},

	_initReply: function() {
		var buttons = $('li.tweet-item .reply a', this.listing);

		buttons.click($.proxy(function(e) {
			var status = $(e.target).parents('li.tweet-item').attr('data-status-id'),
				reply = this.reply.clone(),
				area = $('textarea[name=text]', reply);

			$(e.target).parents('li.tweet-item').append(reply);

			// close on ESCAPE
			var closeOnEscape = function(e) {
				if (e.which != 27) {
					return true;
				}

				reply.remove();

				// only once
				$(document).unbind('keydown', closeOnEscape);

				return true;
			};
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
				}

				// @TODO handle json.error
			}
		});
	},

	_initArchive: function() {
		var buttons = $('li.tweet-item .archive a', this.listing);

		buttons.click($.proxy(function(e) {
			// $.data('status-id') results in math(status-id - 4) so use .attr()
			this.doArchive($(e.target).parents('li.tweet-item').attr('data-status-id'));

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
				}

				// @TODO handle json.error
			}
		})
	}
});
