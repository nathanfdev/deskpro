Orb.createNamespace('DeskPRO.UI');

DeskPRO.UI.Menu2_Instances = {};

DeskPRO.UI.Menu2 = new Orb.Class({

	DisableParentCall: true,
	Implements: [Orb.Util.Options, Orb.Util.Events],

	initialize: function(el, origOptions) {
		el = $(el);


		this.detachedElements = [];
		this.setOptions(origOptions);
		var options = this.options;

		var self = this;
		var positionBy = options.positionBy || el.parent();

		var statusMenu = el;
		var statusMenuH = null;
		var statusBackdrop = null;
		var statusMacroFilter = null;
		var statusListItems = null;
		var currentOpenSubmenu = null;

		var closeStatusMenu = function() {
			closeOpenSubmenu();
      statusBackdrop && statusBackdrop.hide();
			statusMenu.hide();

			self.fireEvent('menuClose', {
				menu: self,
				statusMenu: statusMenu
			});
		};

		var closeStatusMenuAll = function() {
			closeStatusMenu();
			if (options.parentMenu) {
				options.parentMenu.close();
			}
		};

		var updateStatusPos = function() {
			statusMenuH = statusMenu.height();
			if (statusMenu > 500) {
				statusMenu.find('macro-list').css('max-height', 500).css('overflow', 'auto');
				statusMenuH = 500;
			}

			var pos = positionBy.offset();
			if (options.isSubMenu) {
				var l = pos.left + positionBy.outerWidth();
				var t = pos.top + positionBy.height() - 18;
				var sw = statusMenu.width();
				if (l + sw > $(window).width()) {
					l = pos.left - sw - 3;
				}
				if (t + statusMenuH > $(window).height()) {
					t = pos.top - statusMenuH;
				}
				statusMenu.css({
					left: l,
					top: t
				});
			} else {
				if (options.openBelow) {
					statusMenu.css({
						left: pos.left + 6,
						top: pos.top + 18
					});
				} else {
					statusMenu.css({
						left: pos.left + 6,
						top: pos.top - statusMenuH + 3
					});
				}
			}
		};

		var openSubmenu = function(item) {
			closeOpenSubmenu();
			if (!item.hasClass('has-submenu') && !item.hasClass('has-no-submenu')) {
				if (item.find('> .sub-menu')[0]) {
					item.addClass('has-submenu');
				} else {
					item.addClass('has-no-submenu');
				}
			}
			if (!item.hasClass('has-submenu')) {
				return false;
			}

			if (!item.data('menu-inst')) {
				var opts = $.extend(true, {}, origOptions, {
					positionBy: item,
					isSubMenu: true,
					parentMenu: self,
					openBelow: false
				});
				var menuInst = new DeskPRO.UI.Menu2(item.find('> .sub-menu'), opts);
				menuInst.addEvent('menuClose', function() {
					statusMacroFilter.focus();
				});
				item.data('menu-inst', menuInst);
			}

			currentOpenSubmenu = item.data('menu-inst');
			currentOpenSubmenu.open();
			return currentOpenSubmenu;
		};

		var closeOpenSubmenu = function() {
			if (currentOpenSubmenu) {
				var old = currentOpenSubmenu;
				currentOpenSubmenu.close();
				currentOpenSubmenu = null;
				return old;
			} else {
				return false;
			}
		};

		var openStatusMenu = function() {

			// Means we're opening fo rhte first time
			if (!statusBackdrop) {

				if (statusMenu.is('ul')) {
					statusListItems = statusMenu.find('> li').not('.off');
				} else {
					statusListItems = statusMenu.find('> section > .dp-menu-area > ul').find('> li').not('.off');
				}

				statusBackdrop = $('<div class="backdrop"></div>');
				statusBackdrop.appendTo('body');
				statusBackdrop.on('click', function(ev) {
					ev.stopPropagation();
					closeStatusMenu();
					closeStatusMenuAll();
				});
				statusMenu.detach().appendTo('body');
				self.detachedElements.push(statusMenu);

				// Handle macro filtering
				statusMacroFilter = statusMenu.find('.macro-filter').first();

				statusMenu.on('click', 'li', function(ev) {
					ev.stopPropagation();
					self.fireEvent('itemSelected', [{
						menu: self,
						item: $(this)
					}]);
					closeStatusMenu();
					closeStatusMenuAll();
				});

				statusMenu.on('mouseover', 'li', function(ev) {
					statusListItems.removeClass('cursor');
					$(this).addClass('cursor');

					openSubmenu($(this));
				});

				statusMacroFilter.on('keyup', function(ev) {

					var isCtrl = false;
					var current;
					if (ev.ctrlKey && DeskPRO_Window.keyboardShortcuts.isMac) {
						isCtrl = true;
					} else if (ev.altKey && !DeskPRO_Window.keyboardShortcuts.isMac) {
						isCtrl = true;
					}

					var evData = {
						menu: self,
						field: statusMacroFilter,
						isCtrl: isCtrl,
						event: ev,
						cancel: false
					};
					self.fireEvent('filterUpdated', evData);
					if (evData.cancel) {
						return;
					}

					if (ev.keyCode == 13 /* enter key */) {
						ev.preventDefault();
						current = statusListItems.filter('.cursor');
						if (current[0]) {

							if (!openSubmenu(current)) {
								self.fireEvent('itemSelected', [{
									menu: self,
									item: current
								}]);
								closeStatusMenu();
								closeStatusMenuAll();
							}
						}
					} else if (ev.keyCode == 39 /* right arrow */) {
						current = statusListItems.filter('.cursor');
						if (current[0]) {
							openSubmenu(current);
						}
					} else if (ev.keyCode == 37 /* left arrow */) {
						if (options.isSubMenu) {
							closeStatusMenu();
						}
					} else if (ev.keyCode == 27 /* escape key */) {
						ev.preventDefault();
						if (!closeOpenSubmenu()) {
							closeStatusMenu();
						}
					} else if (ev.keyCode == 40 /* down key */ || ev.keyCode == 38 /* up key */) {
						ev.preventDefault();
						var dir = ev.keyCode == 40 ? 'down' : 'up';

						current = statusListItems.filter('.cursor');
						if (!current.length) {
							if (dir == 'down') {
								statusListItems.first().addClass('cursor');
							} else {
								statusListItems.last().addClass('cursor');
							}
						} else {
							var nextIndex = statusListItems.index(current);
							if (dir == 'down') {
								nextIndex++;
							} else {
								nextIndex--;
							}

							if (nextIndex < 0) {
								nextIndex = statusListItems.length-1;
							} else if (nextIndex > (statusListItems.length-1)) {
								nextIndex = 0;
							}

							current.removeClass('cursor');
							statusListItems.eq(nextIndex).addClass('cursor');
						}
					}
				});

				statusMacroFilter.on('keyup', function() {
					var val = $.trim($(this).val());

					if (!val) {
						statusListItems.show().removeClass('off');
						updateStatusPos();
					} else {
						val = val.toLowerCase();

						statusListItems.each(function() {
							if ($(this).text().toLowerCase().indexOf(val) !== -1) {
								$(this).show().removeClass('off');
							} else {
								$(this).hide().addClass('off');
							}
						});
						updateStatusPos();
					}
				});

				self.fireEvent('afterInit', {
					menu: self,
					statusMenu: statusMenu
				});
			}

			self.fireEvent('beforeMenuOpen', {
				menu: self,
				statusMenu: statusMenu
			});

			statusBackdrop.show();
			updateStatusPos();
			statusMenu.show();

			statusMacroFilter.focus();

			self.fireEvent('menuOpen', {
				menu: self,
				statusMenu: statusMenu
			});
		};

		this.open  = openStatusMenu;
		this.close = closeStatusMenu;
		this.closeAll = closeStatusMenuAll;
	},

  destroy: function() {
    this.detachedElements.each(function(el) {
    	el.remove();
		});
    this.detachedElements = [];
    this.options = null;
    this.destroyEvents();
  }
});
