Orb.createNamespace('DeskPRO.Agent.WindowElement.MainMenu');

DeskPRO.Agent.WindowElement.MainMenu.Abstract = new Class({
	Implements: [Events],

	buttonClass: null, // children enter the classname of their button
	buttonEl: null,
	badgeEl: null,
	otherButtonEls: null,
	menuEl: null,

	initialize: function() {
		this.buttonEl = $('#header .nav ul > li.' + this.buttonClass + ':first');
		this.buttonEl.data('menuHandler', this);

		this.badgeEl = $('span.nav-counter:first', this.buttonEl);
		this.menuEl = $('div.wrap-dropdown:first', this.buttonEl);
		this.otherButtonEls = $('#header .nav ul > li:not(.' + this.buttonClass + ')');

		this.init();
	},

	init: function() { },

	updateBadge: function(num) {
		if (num == 0) {
			this.badgeEl.fadeOut(300);
			return;
		}

		var badgeEl = this.badgeEl;
		badgeEl.fadeOut(300, function() {
			badgeEl.html(num).fadeIn(300);
		});
	}
});