Orb.createNamespace('DeskPRO.Agent.PageFragment.ListPane');

DeskPRO.Agent.PageFragment.ListPane.Twitter = new Class({
	Extends: DeskPRO.Agent.PageFragment.ListPane.Basic,

	head: null,
	listing: null,
	note: null,

	initPage: function(el) {
		this.parent(el);

		/* $('li', el).click(function() {
			DeskPRO_Window.runPageRouteFromElement(this);
		}); */

		this.head    = $('.twitter-head', el);
		this.listing = $('.twitter-listing', el);
		this.note    = $('.twitter-note', el);

		this._initOrderBySelectField();
		this._initIncludeFields();

		this._initButtons();
	},

	_initOrderBySelectField: function() {
		$('.display-options select[name=sortbydate]', this.head)
			.change($.proxy(this.reload, this));
	},

	_initIncludeFields: function() {
		$('.display-options input:checkbox', this.head)
			.change($.proxy(this.reload, this));
	},

	getDisplayOptions: function() {
		var options = {
			sortbydate: $('.display-options select[name=sortbydate] option:selected', this.head).attr('name'),
			include: {}
		};

		$('.display-options input:checkbox', this.head).each(function() {
			var field = $(this);
			options.include[field.attr('name')] = field.attr('checked') ? 1 : 0;
		});

		return options;
	},

	reload: function() {
		$.ajax({
			url: this.getMetaData('getStatusesUrl'),
			dataType: 'json',
			data: this.getDisplayOptions(),
			context: this,
			success: function(json) {
				this.listing.html(json.statuses);

				this._initButtons();
			}
		});
	},

	_initButtons: function() {
		this._initNote();
		this._initAssign();
		this._initRetweet();
		this._initReply();
		this._initArchive();
	},

	_initNote: function() {
		var buttons = $('li.tweet-item .note a', this.listing);

		buttons.click($.proxy(function(e) {
			var status = $(e.target).parents('li.tweet-item').attr('data-status-id'),
				note   = this.note.clone(),
				area   = $('textarea[name=text]', note);

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

				this.addNote(status, text);

				e.preventDefault();
				return false;
			}, this));

			note.show();
			area.focus();

			e.preventDefault();
			return false;
		}, this));
	},

	addNote: function(id, text) {
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
			e.preventDefault();
			return false;
		}, this));
	},

	_initReply: function() {
		var buttons = $('li.tweet-item .reply a', this.listing);

		buttons.click($.proxy(function(e) {
			e.preventDefault();
			return false;
		}, this));
	},

	_initArchive: function() {
		var buttons = $('li.tweet-item .archive a', this.listing);

		buttons.click($.proxy(function(e) {
			// $.data('status-id') results in math(status-id - 4) so use .attr()
			this.archive($(e.target).parents('li.tweet-item').attr('data-status-id'));

			e.preventDefault();
			return false;
		}, this));
	},

	archive: function(id) {
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
