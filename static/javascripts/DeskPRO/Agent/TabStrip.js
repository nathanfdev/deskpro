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
		this.tabStrip.mouseup(this._tabStripClick.bind(this));

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
			if (!offset) offset = 0;
			var scroll = scroll_el.scrollLeft();
			scroll += offset;

			if (scroll <= 0) {
				$('#tabNavigationPane').addClass('far-left');
				$('#tabNavigationPane').removeClass('far-right');
			} else {

				var start = scroll - 77;
				var end = start+scroll_el.outerWidth() + 77;

				var x = 0;
				var stop = true;
				var tabLeft, tabRight;
				$('li', tabStrip).each(function() {
					var tabEl = $(this);
					var tabW = tabEl.outerWidth();
					tabLeft = x;
					x += tabW;
					tabRight = x;
				});

				$('#tabNavigationPane').removeClass('far-left');
				if (((tabLeft >= start) && (tabRight <= end))) {
					$('#tabNavigationPane').addClass('far-right');
				} else {
					$('#tabNavigationPane').removeClass('far-right');
				}
			}
		};

		$('#tabNavSelectorLeft').click(function(ev) {
			ev.preventDefault();
			ev.stopPropagation();
			scroll_el.animate({scrollLeft: '-=' + w}, 200, function() {

			});
			updatePosClasses(-200);
		});
		$('#tabNavSelectorRight').click(function(ev) {
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
		menuEl.delegate('li', 'mouseover', function() {
			$('li.over', tabStrip).removeClass('over');
			$('#' + $(this).data('tab-el-id')).addClass('over');
		});
		menuEl.mouseout(function() {
			$('li.over', tabStrip).removeClass('over');
		});
		menuEl.delegate('.close', 'click', function(ev) {
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
			menuEl.empty();
			var tabEl = $('#tabNavigationPane li.activeTabList');

			if ($('#tabNavigationPane').is('.with-overflow') && tabEl.length) {

				var start = scroll_el.scrollLeft();
				var end = start+scroll_el.outerWidth();

				var col = [];

				var x = 0;
				$('li', tabStrip).each(function() {
					var tabEl = $(this);
					var tabW = tabEl.outerWidth();
					var tabLeft = x;

					x += tabW;
					var tabRight = x + tabW;

					if (!((tabLeft >= start) && (tabRight <= end+60))) {
						col.push(tabEl.get(0));
					}
				});

				var lis = $(col);
			} else {
				var lis = $('li', tabStrip);
			}

			lis.each(function() {
				var title = $('a', this).clone();

				var close = $('<a class="close"></a>');
				var fade = $('<div class="bound-fade"></div>');

				var li = $('<li />');
				li.data('tab-id', $(this).data('tab-id'));
				li.data('tab-el-id', $(this).attr('id'));
				li.append(title);
				li.append(close);
				li.append(fade);

				if ($(this).is('.activeTabList')) {
					li.addClass('highlight');
				}

				menuEl.append(li);
			});
		}

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

	alertTab: function(tabIdClass) {
		var el = $('li.' + tabIdClass, this.tabStrip);
		if (!el.length || el.is('.activeTabList') || el.is('.is-alerting')) return;

		el.addClass('is-alerting');
		var timeout = this._alertTabDoHighlight.periodical(1500, this, [el]);
		el.data('alerting-timeout', timeout);
	},

	_alertTabDoHighlight: function(el) {
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
		this.tabManager.addTab(id, {
			html: page.getHtml(),
			page: page,
			title: page.getMetaData('title', 'Untitled'),
			callback_render: function(data, container, tabManager) {
				container = $(container);
				page.fireEvent('render', [container]);
			},
			callback_remove_content: function(data, container, tabManager) {
				page.fireEvent('destroy');
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
			console.log('not click %o', event.target);
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
		var html = '<li id="'+tabData.btnId+'" data-tab-id="'+tabData.id+'" class="tipped ' + tabIdClass;

			if (tabData.page.TYPENAME != 'basic') {
				html += ' ' + tabData.page.TYPENAME;
			}

			if (tabData.page.LOADING_TYPENAME) {
				html += ' ' + tabData.page.LOADING_TYPENAME;
			}

			if (tabData.page.getMetaData('tabTip')) {
				if (tabData.page.getMetaData('tabTip').indexOf('.') === 0) {

					// This means the tip is in an actual element in the tab
					// Note that due to the way the tab manager works, we havent actually rendered
					// the html yet, so this element doesnt exist yet.
					// We'll give the tip an ID, and in _onTabActivate we'll give the tip that ID
					// when the element is rendered.

					tabData.page.setMetaData('fetchTabTip', true);
					html += '" data-tipped="' + tabData.btnId + '_tip' + '" data-tipped-options="inline: true, hook: \'topmiddle\', showDelay: 1000">';
				} else {
					html += '" data-tipped="' + tabData.tabTip + '" data-tipped-options="hook: \'topmiddle\', showDelay: 1000">';
				}
			} else {
				html += '" data-tipped="' + tabData.title + '" data-tipped-options="hook: \'topmiddle\', showDelay: 1000">';
			}

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
			this.removeTabById(tabData.page.meta.tabPlaceholderId);

			$('#' + btnId).replaceWith(li);

			if (!$('li.activeTabList', this.tabStrip).length) {
				li.addClass('activeTabList');
			}
		} else {
			li.appendTo(this.tabStrip);
		}

		this.recalculateScrolling();
	},

	recalculateScrolling: function() {

		var tabPane = $('#tabNavigationPane');

		var w = 0;
		var num = $('li', this.tabStrip).each(function() {
			w += $(this).outerWidth();
		}).length;

		w += 14 * num;

		this.tabStrip.width(w);

		var isScroll    = tabPane.is('.with-overflow');
		var needsScroll = (tabPane.width() < w);

		// Not scrolling and not needed
		if (!isScroll && !needsScroll) {

		// Current scrolling but not needed anymore
		// > Remove scroller
		} else if (isScroll && !needsScroll) {
			console.debug('[TabStrip] Remove scrolling');

			tabPane.removeClass('with-overflow');
			this.tabStrip.scrollLeft(0);
			$('#tabNavigationPane').removeClass('far-left').removeClass('far-right');

		// Not scrolling but needs to now
		// > Add scroller
		} else if (!isScroll && needsScroll) {
			console.debug('[TabStrip] Add scrolling');
			tabPane.addClass('with-overflow');
			$('#tabNavigationPane').addClass('far-left').removeClass('far-right');
		}
	},

	_onTabDeactivate: function(tabData, container, isActivating) {
		// If a tab was removed, the onmouseout was never fired
		// so the tooltip saying its title might still appear
		$('#tiptip_holder').clearQueue().hide();
	},

	_onTabActivate: function(tabData) {
		$('li', this.tabStrip).removeClass('activeTabList');
		var tabEl = $('#' + tabData.btnId).addClass('activeTabList');

		if (tabEl.is('.is-alerting')) {
			tabEl.removeClass('alert-highlight').removeClass('is-alerting');
			var alertingTimeout = tabEl.data('alerting-timeout');
			if (alertingTimeout) {
				window.clearTimeout(alertingTimeout);
				tabEl.data('alerting-timeout', false);
			}
		}
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

		DeskPRO_Window.updateWindowUrlFragment();
	},

	_onTabRemove: function(tabData) {
		$('#tiptip_holder').clearQueue().hide();

		if (tabData.isReplacing) {
			// For Loading pages, the tab element in the strip is replaced
			// with the real tab, so we dont want to remove it now
			return;
		}

		Tipped.remove('#' + tabData.btnId);
		$('#' + tabData.btnId).remove();

		if (tabData.page.meta.routeData && tabData.page.meta.routeData.xhr) {
			tabData.page.meta.routeData.xhr.abort();
		}

		if (tabData.page.meta.routeData && tabData.page.meta.routeData.tabUnload) {
			tabData.page.meta.routeData.tabUnload();
		}

		this.recalculateScrolling();

		DeskPRO_Window.updateWindowUrlFragment();
	}
});
