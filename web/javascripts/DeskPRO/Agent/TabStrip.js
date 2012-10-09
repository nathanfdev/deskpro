Orb.createNamespace('DeskPRO.Agent');

DeskPRO.Agent.TabStrip = new Orb.Class({
	Implements: [Orb.Util.Events, Orb.Util.Options],

	initialize: function(tabStrip, tabManager) {

		window.TAB_STRIP = this;

		this.tabStrip = tabStrip;
		this.tabManager = tabManager;
		this.cancelClickActivate = false;

		this.uniqueCounter = 0;

		var self = this;

		// Mouseup because firefox doesnt respond to click for middle clicks
		this.tabStrip.on('mouseup', this._tabStripClick.bind(this));

		this.tabManager.addEvents({
			addTab: this._onTabAdd.bind(this),
			activateTab: this._onTabActivate.bind(this),
			deactivateTab: this._onTabDeactivate.bind(this),
			removeTab: this._onTabRemove.bind(this)
		});

		// Clicking the scrollers scroll left and right
		var w = (self.tabStrip.parent().width() / 2);
		w = w - (w / 4);

		scroll_el = $('#tabNavigationPane .deskproTabList');
		var ul = $('> ul', scroll_el);

		var updatePosClasses = function(offset) {
			self.recalcScrollControls(offset);
		};

		$('#tabNavSelectorLeft').on('click', function(ev) {
			ev.preventDefault();
			ev.stopPropagation();
			scroll_el.animate({scrollLeft: '-=' + w}, 200, function() {

			});
			updatePosClasses(-200);
		});
		$('#tabNavSelectorRight').on('click', function(ev) {
			ev.preventDefault();
			ev.stopPropagation();
			scroll_el.animate({scrollLeft: '+=' + w}, 200, function() {
			});
			updatePosClasses(200);
		});

		// Scroll wheel should scroll this baby horizontally
		this.tabStrip.mousewheel(function(ev, delta) {
			if (delta > 0) {
				scroll_el.animate({scrollLeft: '+=' + w}, 100);
				updatePosClasses(100);
			} else {
				scroll_el.animate({scrollLeft: '-=' + w}, 100);
				updatePosClasses(-100);
			}
		});

		var menuEl = $('<ul id="dp_tabstrip_menu" />').hide().appendTo('body');
		menuEl.on('mouseover', 'li', function() {
			$('li.over', tabStrip).removeClass('over');
			$('#' + $(this).data('tab-el-id')).addClass('over');
		});
		menuEl.on('mouseout', function() {
			$('li.over', tabStrip).removeClass('over');
		});
		menuEl.on('click', '.close', function(ev) {
			ev.stopPropagation();
			ev.preventDefault();

			var item = $(this).closest('li');
			var tabId = item.data('tab-id');

			self.tabManager.removeTab(tabId);

			updateMenuItems();
			if (!$('li', menuEl).length) {
				self.tabMenu.close();
			}
		});

		var updateMenuItems = function() {
			var tabEl = $('#tabNavigationPane li.activeTabList');

			if ($('#tabNavigationPane').is('.with-overflow') && tabEl.length) {

				var scroll = scroll_el.scrollLeft();
				var start = scroll - (18 + 4);
				var end = start+scroll_el.outerWidth() + (18 + 18 + 5); /* scroll btn, drop, some extra margin */

				var col = [];

				var x = 0;
				$('li', tabStrip).each(function() {
					var tabEl = $(this);
					var tabW = tabEl.outerWidth();
					var tabLeft = x;

					x += tabW;
					var tabRight = x + tabW;

					if (!((tabLeft >= start) && (tabLeft <= end))) {
						col.push(tabEl.get(0));
					}
				});

				var lis = $(col);
			} else {
				var lis = $('li', tabStrip);
			}

			$('> li', menuEl).hide().removeClass('shown');
			lis.each(function() {
				$('#' + $(this).attr('id') + '_mi').show().addClass('shown');
			});
		}
		this.updateMenuItems = updateMenuItems;

		this.tabMenu = new DeskPRO.UI.Menu({
			triggerElement: $('#tabDropdownPicker'),
			menuElement: menuEl,
			onBeforeMenuOpened: function() {
				updateMenuItems();
			},
			onItemClicked: function(info) {
				var item = $(info.itemEl);
				var tabId = item.data('tab-id');
				var tabEl = $('#' + tabId)
				self.activateTabById(tabId);

				// Make sure if the tabPane is scrolling, that the tab is in view
				var tabEl = $('#tabNavigationPane li.activeTabList');
				if ($('#tabNavigationPane').is('.with-overflow') && tabEl.length) {
					var tabPos = tabEl.position().left;
					var tabW = tabEl.width();
					var w = scroll_el.width();
					var scrollL = scroll_el.scrollLeft();

					if (tabPos < scrollL || (tabPos+tabW) > (scrollL+w)) {
						scroll_el.scrollLeft(tabPos);
						updatePosClasses();
					}
				}
			},
			onClose: function() {
				$('li.over', tabStrip).removeClass('over');
			}
		});
	},

	isTabInView: function(tab) {
		this.updateMenuItems();
		var check = $('#' + tab.btnId);
		if (check.length) {
			return true;
		}

		return false;
	},

	alertTab: function(tab) {
		var tabId = tab.btnId;

		var el = $('#' + tabId);
		if (!el.length || el.is('.activeTabList') || el.is('.is-alerting')) return;

		el.addClass('is-alerting');
		var timeout = this._alertTabDoHighlight.periodical(700, this, [el]);
		el.data('alerting-timeout', timeout);

		this.updateAlertingMenuItems();
	},

	clearAlertTab: function(tab) {
		var tabId = tab.btnId;

		var el = $('#' + tabId);
		if (!el.length) return;

		el.removeClass('alert-highlight').removeClass('is-alerting');

		var timeout = el.data('alerting-timeout');
		if (timeout) {
			window.clearTimeout(timeout);
		}

		el.data('alerting-timeout', null);

		var menuEl = $('#' + tabId + '_mi');
		menuEl.removeClass('is-alerting');
		this.updateAlertingMenuItems();
	},

	updateAlertingMenuItems: function() {
		this.updateMenuItems();
		var lis = $('#dp_tabstrip_menu li.is-alerting.shown');

		var el = $('#tabDropdownPicker');
		var menu = $('#dp_tabstrip_menu');

		if (lis.length) {
			if (!el.is('.is-alerting')) {
				el.addClass('is-alerting');

				var timeout = (function() { el.toggleClass('alert-highlight'); menu.toggleClass('alert-highlight'); }).periodical(700, this);
				el.data('alerting-timeout', timeout);
			}
		} else {
			var timeout = el.data('alerting-timeout');
			if (timeout) {
				window.clearTimeout(timeout);
			}
			el.data('alerting-timeout', null);
			el.removeClass('is-alerting').removeClass('alert-highlight');
			menu.removeClass('alert-highlight');
		}
	},

	_alertTabDoHighlight: function(el) {
		var menuEl = $('#' + el.attr('id') + '_mi').addClass('is-alerting');
		this.updateAlertingMenuItems();
		el.toggleClass('alert-highlight');
	},

	getTabs: function() {
		return this.tabManager.getTabs();
	},

	getActiveTab: function() {
		return this.tabManager.getActiveTab();
	},

	activateTabById: function(tabId) {
		this.tabManager.activateTab(tabId);
	},

	removeTabById: function(tabId) {
		this.tabManager.removeTab(tabId);
	},

	findTabByPage: function(page) {
		var tabId = false;
		Object.each(this.tabManager.getTabs(), function(v, k) {
			if (v.page == page) {
				tabId = k;
				return false;
			}
		});

		return tabId;
	},

	findTabByFragment: function(fragment) {
		var tabId = false;

		Object.each(this.tabManager.getTabs(), function(tab) {
			if (tab.page.getMetaData('url_fragment') == fragment) {
				tabId = tab.id;
				return false;
			}
		});

		return tabId;
	},

	getTabByFragment: function(fragment) {
		var tabId = this.findTabByFragment(fragment);
		if (!tabId) return null;

		return this.getTabById(tabId);
	},

	findTabByRouteUrl: function(routeUrl) {
		var tabId = false;
		Object.each(this.tabManager.getTabs(), function(tab) {
			if (tab.page.getMetaData('routeUrl') == routeUrl) {
				tabId = tab.id;
				return false;
			}
		});

		return tabId;
	},

	getTabByRouteUrl: function(routeUrl) {
		var tabId = this.findTabByRouteUrl(routeUrl);
		if (!tabId) return null;

		return this.getTabById(tabId);
	},

	getTabById: function(tabId) {
		return this.tabManager.getTab(tabId);
	},

	addTab: function(page) {

		var id = 'tab' + this.uniqueCounter++;

		page.meta.tabId = id;

		this.tabManager.addTab(id, {
			html: page.getHtml(),
			page: page,
			title: page.getMetaData('title', 'Untitled'),
			callback_render: function(data, container, tabManager) {
				container = $(container);
				page.fireEvent('render', [container]);
			},
			callback_remove_content: function(data, container, tabManager) {
				if (data.isInserted && data.isInited) {
					page.fireEvent('destroy');
				}
			},
			callback_activate: function() {
				page.fireEvent('activate');
			},
			callback_deactivate: function() {
				page.fireEvent('deactivate');
			}
		});

		return id;
	},

	addTabPlaceholder: function(url, routeData) {
		var html = DeskPRO_Window.util.getPlainTpl($('#tab_loading_template'));

		var page = DeskPRO_Window.createPageFragment(html, 'DeskPRO.Agent.PageFragment.Page.Loading');
		page.meta.routeUrl = url;
		page.meta.routeData = routeData;
		page.TYPENAME_FOR = routeData.master;
		page.TAB_FOR_ID = routeData.masterTag;

		if (routeData.url_fragment) {
			page.meta.url_fragment = routeData.url_fragment;
		}

		if (routeData.title) {
			page.meta.title = routeData.title;
		}
		if (routeData.forTypename) {
			page.LOADING_TYPENAME = routeData.forTypename;
		}

		var id = this.addTab(page);

		if (routeData.tabLoad) {
			routeData.tabLoad();
		}

		return id;
	},

	_tabStripClick: function(event) {

		if (this.cancelClickActivate) {
			this.cancelClickActivate = false;
			return;
		}

		this.cancelClickActivate = true;

		var el_click = $(event.target);

		if (el_click.is('li')) {
			var el = el_click;
		} else if (el_click.parent().is('li')) {
			var el = el_click.parent();
		} else {
			var el = el_click.parentsUntil('li');
			if (el.length) {
				el = el.parent(); // jquery doesnt include the actual parent in parentsUntil
			}
		}

		// If its not a tab, we can just ignore the event
		if (!el.is('li')) {
			DP.console.log('not click %o', event.target);
			return;
		}

		event.preventDefault();
		event.stopPropagation();

		var tabId = el.data('tab-id');

		// If the clicked thing was the close button, or if its a middle-click...
		if (el_click.is('.close') || event.which == 2 || event.isDbl) {
			var tab = this.getTabById(tabId);
			if (tab.page && tab.page.fireEvent) {
				event.deskpro = {cancelClose: false};
				tab.page.fireEvent('closeTab', [event, tab]);

				if (event.deskpro.cancelClose) {
					return;
				}
			}

			tab.isCloseClick = true;
			this.tabManager.removeTab(tabId);
			tab.isCloseClick = false;

			this.cancelClickActivate = false;

			return;
		}

		// Otherwise activate the tab
		this.tabManager.activateTab(tabId);

		this.cancelClickActivate = false;
	},

	_onTabAdd: function(tabData) {
		tabData.btnId = tabData.id;

		if (!tabData.page.getMetaData('url_fragment') && tabData.page.TYPENAME != 'loading') {
			//tabData.page.setMetaData('fragment', tabData.id);
		}

		var tabIdClass = tabData.page.getMetaData('tabIdClass', '');
		var html = '<li id="'+tabData.btnId+'" data-tab-id="'+tabData.id+'" class="' + tabIdClass;

			if (tabData.page.TYPENAME != 'basic') {
				html += ' ' + tabData.page.TYPENAME;
			}

			if (tabData.page.LOADING_TYPENAME) {
				html += ' ' + tabData.page.LOADING_TYPENAME;
			}

			html += '">';
			html += '<a>';
				html += '<span class="tab-title">'+tabData.title+'</span>';
			html += '</a>';
			html += '<span class="bound-fade"></span>';
			html += '<span class="close"></span>';
		html += '</li>';

		var li = $(html);

		if (tabData.page && tabData.page.meta.tabPlaceholderId) {
			var otherTab = this.getTabById(tabData.page.meta.tabPlaceholderId);
			var btnId = otherTab.btnId;

			loadingTabData = this.getTabById(tabData.page.meta.tabPlaceholderId);
			loadingTabData.isReplacing = true;

			this.tabManager.removeTab(tabData.page.meta.tabPlaceholderId, true);

			$('#' + btnId).replaceWith(li);

			if (!$('li.activeTabList', this.tabStrip).length) {
				li.addClass('activeTabList');
			}
		} else {
			li.appendTo(this.tabStrip);
		}

		// Add the menu button
		var title = $('a', li).clone();
		var close = $('<a class="close"></a>');
		var fade = $('<div class="bound-fade"></div>');

		var menuLi = $('<li />');
		menuLi.attr('id', $(li).attr('id') + '_mi');//mi for menu item ;-)
		menuLi.data('tab-id', $(li).data('tab-id'));
		menuLi.data('tab-el-id', $(li).attr('id'));
		menuLi.append(title);
		menuLi.append(close);
		menuLi.append(fade);

		$('#dp_tabstrip_menu').append(menuLi);

		this.recalculateScrolling();
	},

	recalculateScrolling: function() {

		var tabPane = $('#tabNavigationPane');

		var w = this.getTabsWidth();
		this.tabStrip.width(w);

		var isScroll    = tabPane.is('.with-overflow');
		var needsScroll = (tabPane.width() < w);

		// Not scrolling and not needed
		if (!isScroll && !needsScroll) {

		// Current scrolling but not needed anymore
		// > Remove scroller
		} else if (isScroll && !needsScroll) {
			DP.console.debug('[TabStrip] Remove scrolling');

			tabPane.removeClass('with-overflow');
			this.tabStrip.scrollLeft(0);
			$('#tabNavigationPane').removeClass('far-left').removeClass('far-right');

		// Not scrolling but needs to now
		// > Add scroller
		} else if (!isScroll && needsScroll) {
			DP.console.debug('[TabStrip] Add scrolling');
			tabPane.addClass('with-overflow');
			$('#tabNavigationPane').removeClass('far-left').removeClass('far-right');
			this.recalcScrollControls();
		}
	},

	recalcScrollControls: function(offset) {
		var scroll_el = $('#tabNavigationPane .deskproTabList');
		var ul = $('> ul', scroll_el);

		if (!offset) offset = 0;
		var scroll = scroll_el.scrollLeft();
		scroll += offset;

		if (scroll <= 0) {
			$('#tabNavigationPane').addClass('far-left');
			$('#tabNavigationPane').removeClass('far-right');
		} else {

			var start = scroll + 18 + 4;
			var end = start+scroll_el.outerWidth() + 18 + 18 + 4; /* scroll btn, drop, some extra margin */

			$('#tabNavigationPane').removeClass('far-left');

			var lastTab = $('#tabNavigationPane li').last();
			var tabLeft  = lastTab.offset().left;
			var tabRight = tabLeft + lastTab.outerWidth() + 4;

			if (((tabLeft >= start) && (tabRight <= end))) {
				$('#tabNavigationPane').addClass('far-right');
			} else {
				$('#tabNavigationPane').removeClass('far-right');
			}
		}

		this.updateAlertingMenuItems();
	},

	getTabsWidth: function() {
		var w = 0;
		var num = $('li', this.tabStrip).each(function() {
			w += $(this).outerWidth() + 4; /* 4px margin-right */
		}).length;

		w += 18 + 5; // the dropdown menu is 18px, plus a bit of margin

		return w;
	},

	_onTabDeactivate: function(tabData, container, isActivating) {
		// If a tab was removed, the onmouseout was never fired
		// so the tooltip saying its title might still appear
		$('#tiptip_holder').clearQueue().hide();
	},

	_onTabActivate: function(tabData) {
		$('li', this.tabStrip).removeClass('activeTabList');
		var tabEl = $('#' + tabData.btnId).addClass('activeTabList');

		this.clearAlertTab(tabData);

		// Now that the tab has been rendered, we'll have access to
		// the tip element, so we should give it the appropriate ID
		// so Tipped can find it
		if (tabData.page.getMetaData('fetchTabTip')) {
			tabData.page.setMetaData('fetchTabTip', null);
			var tipId = tabData.btnId + '_tip';
			if (!document.getElementById(tipId)) {
				$('#' + tabData.wrapperId + ' ' + tabData.page.getMetaData('tabTip')).attr('id', tipId);
			}
		}

		if (!tabData.page.meta.routeData.noUpdateHash) {
			DeskPRO_Window.updateWindowUrlFragment();
		}
	},

	_onTabRemove: function(tabData) {
		$('#tiptip_holder').clearQueue().hide();

		if (tabData.isReplacing) {
			// For Loading pages, the tab element in the strip is replaced
			// with the real tab, so we dont want to remove it now
			return;
		}

		$('#' + tabData.btnId).remove();

		if (tabData.page.meta.routeData && tabData.page.meta.routeData.xhr) {
			tabData.page.meta.routeData.xhr.abort();
		}

		if (tabData.page.meta.routeData && tabData.page.meta.routeData.tabUnload) {
			tabData.page.meta.routeData.tabUnload();
		}

		$('#' + tabData.btnId + '_mi');
		this.recalculateScrolling();

		if (tabData.page.TYPENAME != 'loading') {
			DeskPRO_Window.updateWindowUrlFragment();
		}
	}
});
