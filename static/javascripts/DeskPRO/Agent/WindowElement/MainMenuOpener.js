Orb.createNamespace('DeskPRO.Agent.WindowElement');

DeskPRO.Agent.WindowElement.MainMenuOpener = new Class({
	initialize: function() {

		var self = this;

		// Click opens the submenu
		$('#header .nav > .wrapper-top-bar > ul > li').each(function() {
			var li = $(this);
			var class = self.getMenuHandlerClass(li);
			if (class) {
				var handler = new class();
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
		var currentOpen = $('#header .nav > .wrapper-top-bar > ul > li').removeClass('on');
		if (currentOpen.length && currentOpen.data('menuHandler')) {
			currentOpen.data('menuHandler').fireEvent('menuClose');
		}

		$('#header .nav > .wrapper-top-bar > ul > li').removeClass('on off');
		$('#header .nav > .wrapper-top-bar > ul > li .wrap-dropdown').hide();
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
			$('#header .nav > .wrapper-top-bar > ul > li:not(.on)').addClass('off');

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