Orb.createNamespace('DeskPRO.Agent.WindowElement');

DeskPRO.Agent.WindowElement.MainMenuOpener = new Orb.Class({
	Implements: [Orb.Util.Options],

	initialize: function(options) {

		this.allMenus = null;
		this.options = {
			menuSelectors: []
		};

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
		this.allMenus.filter(':not(.no-menu)').each(function() {
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

	getMenuHandler: function(menuEl) {
		menuEl = $(menuEl);
		var menuHandler = menuEl.data('menuHandler');

		return menuHandler;
	},

	getMenuHandlerClass: function(li) {
		var handler = li.data('menu-handler');
		if (handler) {
			if (DeskPRO_Window.DEBUG.disableMenuHandlers) {
				if (!DeskPRO_Window.DEBUG.enableMenuHandlers || DeskPRO_Window.DEBUG.enableMenuHandlers.indexOf(li.data('menu-handler')) === -1) {
					return false;
				}
			}
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

		// If its a link with a href, then
		// dont fuss around with anything here
		if ($(event.target).is('a[href]')) {
			return;
		}

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
				if (!li.is('.has-opened')) {
					li.data('menuHandler').fireEvent('menuFirstOpen');
					li.addClass('has-opened');
				}
				li.data('menuHandler').fireEvent('menuOpen');
			}
		}

		if (event) {
			event.preventDefault();
			event.stopPropagation();
		}
	}
});