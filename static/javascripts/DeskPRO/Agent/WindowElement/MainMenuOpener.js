Orb.createNamespace('DeskPRO.Agent.WindowElement');

DeskPRO.Agent.WindowElement.MainMenuOpener = new Class({
	Implements: [Options],
	options: {
		menuSelectors: []
	},

	allMenus: null,

	initialize: function(options) {

		var self = this;

		this.options.menuSelectors.push('#header .nav > .wrapper-top-bar > ul > li');
		this.options.menuSelectors.push('#header .box-header.notifications');
		this.options.menuSelectors.push('#header .box-header.current-user');
		this.options.menuSelectors.push('#header .box-header.actions > .wrapper-top-bar > ul > li.with-menu');
		this.options.menuSelectors.push('#window_search_type');
		this.options.menuSelectors.push('#window_search_form');
		this.allMenus = $();

		for (var i = 0; i < this.options.menuSelectors.length; i++) {
			var a = this.options.menuSelectors[i];
			if (typeOf(a) == 'string') {
				a = $(a);
			}

			this.allMenus = this.allMenus.add(a);
		}

		// Click opens the submenu
		this.allMenus.each(function() {
			var li = $(this);
			var class = self.getMenuHandlerClass(li);
			if (class) {
				var handler = new class(li, {'mainMenuOpener': self});
				li.data('menuHandler', handler);
			}

		}).click(function(event) {
			var li = $(this);
			self.openMenu(li, event);
		});

		$(document).click(function(event) {
			self.closeMenus(event);
		});
	},

	getMenuHandlerClass: function(li) {
		if (li.data('menu-handler')) {
			return Orb.getNamespacedObject(li.data('menu-handler'));
		}

		return false;
	},

	closeMenus: function(event) {

		if (event && event._noCloseMenu) return;

		// Fire close events on open menu
		var currentOpen = this.allMenus.filter('.on:first').removeClass('on');
		if (currentOpen.length && currentOpen.data('menuHandler')) {
			currentOpen.data('menuHandler').fireEvent('menuClose');
		}

		this.allMenus.removeClass('on off');
		$('.wrap-dropdown', this.allMenus).hide();
	},

	openMenu: function(li, event) {

		// If we're already open, clicking should toggle to closed
		var doopen = true;
		if (li.is('.on')) {
			doopen = false;
		}

		this.closeMenus();

		if (doopen) {
			$('.wrap-dropdown', li).show();
			li.addClass('on');
			var ul = li.parent();
			$('> li:not(.on)', ul).addClass('off');

			if (li.data('menuHandler')) {
				li.data('menuHandler').fireEvent('menuOpen');
			}
		}

		if (event) {
			event.preventDefault();
			event.stopPropagation();
		}
	}
});