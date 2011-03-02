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

		// Capture clicks for on dataRoute's
		var self = this;
		this.menuEl.delegate('[data-route]', 'click', function(ev) {
			var evData = {'event': ev, 'cancelClose': false, preventDefault: true };
			self.fireEvent('clickRoute', [ev, evData]);

			if (evData.preventDefault) {
				ev.preventDefault();
			}
			if (evData.cancelClose) {
				return;
			}

			DeskPRO_Window.runPageRouteFromElement(this);
			self.closeMenu();
		});

		this.menuEl.click(function(ev) {
			//cancel bubbling to document which clsoes the menu
			ev.stopPropagation();
		});

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
	},

	closeMenu: function() {
		DeskPRO_Window._closeWindowMenus();
	}
});