Orb.createNamespace('DeskPRO.Agent.PageFragment.ListPane');

DeskPRO.Agent.PageFragment.ListPane.Twitter = new Class({
	Extends: DeskPRO.Agent.PageFragment.ListPane.Basic,

	head: null,
	listing: null,

	initPage: function(el) {
		this.parent(el);

		/* $('li', el).click(function() {
			DeskPRO_Window.runPageRouteFromElement(this);
		}); */

		this.head = $('.twitter-head', el);
		this.listing = $('.twitter-listing', el);

		this._initOrderBySelectField();
		this._initIncludeFields();
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
			options.include[field.attr('name')] = field.attr('checked');
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
			}
		});
	}
});
