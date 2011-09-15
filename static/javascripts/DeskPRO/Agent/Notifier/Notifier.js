Orb.createNamespace('DeskPRO.Agent.Notifier');

DeskPRO.Agent.Notifier.Notifier = new Class({
	Implements: [Events, Options],

	options: {
		notifySummaryButton: null,
		notifyList: null
	},

	button: null,
	list: null,

	notifyTypes: [],

	initialize: function(options) {
		if (options) this.setOptions(options);

		this.button = this.options.notifySummaryButton;
		this.list = this.options.notifyList;

		this.button.click(this.toggleList.bind(this));

		var self = this;
		$('span.dismiss', this.list).live('click', function () {
			self.handleDismissClick($(this));
		});

		//this.notifyTypes.push(new DeskPRO.Agent.Notifier.Types.Ticket());

		Array.each(this.notifyTypes, function(t) {
			t.addEvent('listUpdated', this.updateListForType, this);
		});
	},

	handleDismissClick: function (el) {
		var li = el.parent();
		var ul = li.parent();
		var section = ul.parent();

		li.slideUp(function() {
			li.remove();

			if (!$('li', ul).length) {
				section.slideUp(function() {
					section.remove();
				});
			}
		});
	},

	updateSummaryLine: function(summary) {
		this.button.text(summary);
		this.flashButton();
	},

	flashButton: function() {
		this.button.effect("pulsate", {}, 500);
	},

	toggleList: function() {
		if (this.list.is(':visible')) {
			this.hideList();
		} else {
			this.showList();
		}
	},

	updateListForType: function(type) {
		var all = [];

		Array.each(this.notifyTypes, function(t) {
			var summary = t.getSummary();
			if (summary) {
				all.push(summary);
			}
		});

		all = all.join(', ');
		this.updateSummaryLine(all);

		$('li > ul > li', this.list).append('<span class="dismiss">dismiss</span>');
	},

	showList: function() {
		if (this.list.is(':visible')) {
			return;
		}

		this.list.css({
			position: 'absoloute',
			left: 0,
			top: 0
		});
		this.list.position({
			my: 'right top',
			at: 'right bottom',
			of: this.button,
			offset: '1 -2'
		}).slideDown(300);

		this.button.addClass('on');
	},

	hideList: function() {
		if (!this.list.is(':visible')) {
			return;
		}

		var button = this.button;
		this.list.slideUp(200, function() {
			button.removeClass('on');
		});
	}
});
