Orb.createNamespace('DeskPRO.Agent.PageFragment.ListPane');

DeskPRO.Agent.PageFragment.ListPane.TwitterStatus = new Class({
	Extends: DeskPRO.Agent.PageFragment.ListPane.BasicTwitter,

	note: null,
	reply: null,

	initPage: function(el) {
		this.parent(this.el);

		this.note = $('.twitter-note', el);
		this.reply = $('.twitter-reply', el);

		this._initPhotos();
	},

	_initHead: function() {
		this._initOrderBySelectField();
		this._initIncludeFields();
	},

	_initStatusControls: function() {
		this._initAddNote();
		this._initAssign();
		this._initRetweet();
		this._initReply();
		this._initArchive();
	},

	_afterLoading: function() {
		this._initPhotos();
		this._initStatusControls();
	},

	_initOrderBySelectField: function() {
		$('.display-options select[name=sortbydate]', this.head)
			.change($.proxy(this.reload, this));
	},

	_initIncludeFields: function() {
		$('.display-options input:checkbox', this.head)
			.change($.proxy(this.reload, this));
	},

	_initPhotos: function() {
		$('div.photo', this.el).click(function() {
			DeskPRO_Window.runPageRouteFromElement(this);
		});
	},

	_getDisplayOptions: function() {
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
			url: this.getMetaData('statusListUrl'),
			dataType: 'json',
			data: this._getDisplayOptions(),
			context: this,
			success: function(json) {
				this.listing.html(json.statuses);
				this._afterLoading();
			}
		});
	}
});
