Orb.createNamespace('DeskPRO.Agent.PageFragment');

/**
 * A generic page fragment is any kind of page we'll spit into the content
 * area of the currently loaded page. For example, tabs, lightbox content etc.
 *
 * Each page can have it's own resources that should be loaded before the HTML
 * for it is rendered (though that responsibility is up to whatever uses the PageFragment).
 */
DeskPRO.Agent.PageFragment.Basic = new Orb.Class({

	Implements: [Orb.Util.Events],

	initializeProperties: function() {

	},

	updateUi: function() {
		var x;
		if (!this.IS_ACTIVE) {
			return;
		}
		if (this.wrapper) {
			if (!this.scrollHandlers) {
				this.scrollHandlers = this.wrapper.find('div.with-scroll-handler');
			}
			for (x = 0; x < this.scrollHandlers.length; x++) {
				var sh = $(this.scrollHandlers[x]).data('scroll_handler');
				if (sh && sh.updateSize) {
					sh.updateSize();
				}
			};
		}

		this.fireEvent('updateUi');
	},

	initialize: function(html) {
		var self = this;

		this.pageUid = Orb.uuid();
		this.ZONE = 'agent';
		this.TYPENAME = 'basic';
		this.IS_ACTIVE = false;

		this.allowDupe = false;
		this.scripts = [];
		this.stylesheets = [];
		this.html = '';
		this.meta = {};
		this.urls = {};

		this.destroyObjects = [];

		this.featureSelectors = {
			routes: [],
			times: []
		};

		this.resizerInterval = window.setInterval(function() {
			self.updateUi();
		}, 1100);

		this.initializeProperties();

		if (html) {
			this.html = html;
		}

		this.addEvent('activate', function() {
			this.IS_ACTIVE = true;
			DeskPRO_Window.getMessageBroker().sendMessage('page-fragment.activated', { page: this });
			this.updateUi();
		}, this);
		this.addEvent('deactivate', function() {
			this.IS_ACTIVE = false;
			DeskPRO_Window.getMessageBroker().sendMessage('page-fragment.deactivated', { page: this });
			if (this.wrapper) {
				this.wrapper.find('.with-handler').trigger('dp_hide');
			}
		}, this);

		// Auto-init
		this.addEvent('render', function(wrapper) {
			self.wrapper = wrapper;
			wrapper.data('page-fragment', self);
			wrapper.addClass('with-page-fragment');
			this.fragmentElement = wrapper;

			DeskPRO_Window.initInterfaceServices(wrapper);

			if (!this.noDeleteHtmlString) {
				delete this.html;
			}

			this.initPage(wrapper);
			this.initApps();

			DeskPRO_Window.TabBar.rescanTitles();

			DeskPRO_Window.getMessageBroker().sendMessage('agent.ui.tabinit.' + this.TYPENAME, this);
		}, this);

		var self = this;

		// Standard hook methods
		this.addEvent('activate', this.activate);
		this.addEvent('deactivate', this.deactivate);
		this.addEvent('destroy', this.destroyPage);

		this.init();

		this.addEvent('activate', function() {
			this.clearAlerts();
		}, this);

		this.addEvent('destroy', function() {
			self.cleanupApps();
		});
		this.addEvent('destroy', function() {
			this.scrollHandlers = [];
			if (self.resizerInterval) {
				window.clearInterval(self.resizerInterval);
			}

			if (self.wrapper) {
				self.wrapper.find('.with-scroll-handler').each(function() {
					var sh = $(this).data('scroll_handler');
					if (sh) {
						sh.destroy();
						$(this).data('scroll_handler', null);
					}
				});
				self.wrapper.find('.with-select2').each(function() {
					$(this).select2('destroy');
				});
				self.wrapper.find('textarea.with-redactor').each(function() {
					var obj = $(this).getObject();
					if (obj) {
						$(this).getObject().destroy();
					}
				});

				self.wrapper.data('with-page-fragment', null);
			}

			if (self.destroyObjects) {
				var i;
				for (i = 0; i < self.destroyObjects.length; i++) {
					if (self.destroyObjects[i] && self.destroyObjects[i].destroy) {
						self.destroyObjects[i].destroy();
					}
				}
				self.destroyObjects = null;
			}

			DeskPRO_Window.getMessageBroker().removeTaggedListeners(self.OBJ_ID);
			if (self.wrapper) {
				self.wrapper.find('.with-handler').each(function() {
					var h = $(this).data('handler');
					if (h) {
						h.destroy();
					}
				});
				self.wrapper.empty();
			}
		});
		this.addEvent('destroy', this.destroy);

		if (this.meta.routeData && this.meta.routeData.routeTriggerEl && this.meta.routeData.toggleOpenClass) {
			this.addEvent('destroy', function() {
				this.meta.routeData.routeTriggerEl.removeClass(this.meta.routeData.toggleOpenClass);
			}, this);
		}
	},

	/**
	 * Empty hook method for children
	 */
	init: function() { },

	/**
	 * Called when the fragment has been activated (comes into view).
	 */
	activate: function() { },

	/**
	 * Called when the fragment is deactivated (hidden from view)
	 */
	deactivate: function() { },

	/**
	 * Register an object that we "own."
	 *
	 * When this page is destroyed, all of these owned objects
	 * are destroyed as well.
	 *
	 * @param obj
	 */
	ownObject: function(obj) {
		if (obj.destroy && this.destroyObjects) {
			this.destroyObjects.push(obj);
		}
	},

	/**
	 * Set metadata about this page.
	 *
	 * @param mixed name Either a string name to use with value, or an object of key/value pairs
	 * @param mixed value Only used if name is a string, the value to set
	 */
	setMetaData: function(name, value) {
		// Assigning multiple values from a hash
		if (value === undefined && typeOf(name) == 'object') {
			this.meta = Object.merge(this.meta, name);
			this.initMetaData();
		} else {
			this.meta[name] = value;
		}
	},

	initMetaData: function() {

	},

	/**
	 * Get a hash of all the metadata.
	 *
	 * @return {Object}
	 */
	getAllMetaData: function() {
		return this.meta;
	},



	/**
	 * Get a specific piece of metadata.
	 *
	 * @param {String} name The name of the data you want
	 * @param mixed default_value The value to return if the metadata is undefined
	 */
	getMetaData: function(name, default_value) {
		if (default_value === undefined) {
			default_value = null;
		}

		if (this.meta[name] === undefined) {
			return default_value;
		}

		return this.meta[name];
	},


	/**
	 * Get a URL pattern
	 */
	getUrl: function(name, vars) {

		if (!this.meta.urls) {
			DP.console.error('Unknown url name %s (no urls set)', name);
			return null;
		}

		if (!this.meta.urls[name]) {
			DP.console.error('Unknown url name %s', name);
			return null;
		}

		var url = this.meta.urls[name];
		if (vars) {
			Object.each(vars, function(v,k) {
				url = url.replace('{'+k+'}', v);
			});
		}

		return url;
	},



	/**
	 * Get the scripts required by this fragment.
	 *
	 * @return {Array}
	 */
	getScripts: function() {
		return this.scripts;
	},



	/**
	 * Get stylesheets required by this fragment
	 *
	 * @return {Array}
	 */
	getStylesheets: function() {
		return this.stylesheets;
	},



	/**
	 * Get the HTML source for this fragment.
	 *
	 * @return {String}
	 */
	getHtml: function() {
		return this.html;
	},



	/**
	 * Should be called after all resources are laoded and after the
	 * HTML is in the dom.
	 *
	 * @param {jQuery} el The wrapper element
	 */
	initPage: function(el) {
		this.wrapper = el;
	},



	/**
	 * Called after the page should be destroyed. Any specific cleanup required can be done
	 * here if for example an element was moved during initPage etc.
	 */
	destroyPage: function() {

	},


	/**
	 * Get an element within this page by ID, using the baseId set in metadata if avail
	 *
	 * @param id
	 */
	getEl: function(id) {
		if (this.meta && this.meta.baseId) {
			id = this.meta.baseId + '_' + id;
		}

		return $('#' + id);
	},


	/**
	 * If this page is part of a tabstrip, return its tab id
	 *
	 * @return {String}
	 */
	getTabId: function() {
		if (this.meta.tabId) {
			return this.meta.tabId;
		}

		return null;
	},

	/**
	 * If this page is part of a tabstrip, return the tab object its
	 * attached to.
	 *
	 * @return {Object}
	 */
	getTab: function() {
		var tabId = this.getTabId();
		if (!tabId) return null;

		return DeskPRO_Window.TabBar.getTab(tabId);
	},


	/**
	 * Activates flashing on the tab to alert of a change or something that requires attention
	 */
	alertTab: function() {
		var tab = this.getTab();
		if (!tab) return;

		DeskPRO_Window.TabBar.alertTab(tab);
	},


	/**
	 * Close this tab
	 */
	closeSelf: function() {
		DeskPRO_Window.removePage(this);
	},


	/**
	 * Sroll to top
	 */
	goTabTop: function() {
		if (this.wrapper) {
			this.wrapper.find('div.layout-content').trigger('goscrolltop');
		}
	},


	/**
	 * Scroll to bottom
	 */
	goTabBottom: function() {
		if (this.wrapper) {
			this.wrapper.find('div.layout-content').trigger('goscrollbottom');
		}
	},

	getAlertId: function() {
		if (this.meta && this.meta.alert_id) {
			return this.meta.alert_id;
		}
		return null;
	},

	clearAlerts: function() {
		var id = this.getAlertId();
		if (!id) {
			return;
		}

		DeskPRO_Window.notifications.removeRowById(id);
		DeskPRO_Window.notifications.removeRowByClass(id);
	},

	initApps: function() {
		var platform = DeskPRO_Window.getAppPlatform();
		if (!platform) {
			console.warn("platform not available");
			return;
		}

		platform.onFragmentStarted(this);
	},

	cleanupApps: function() {
		var platform = DeskPRO_Window.getAppPlatform();
		if (!platform) {
			console.warn("platform not available");
			return;
		}

		platform.onFragmentEnded(this);
	},

	updateAppsSidebar: function() {
		var el = this.getEl('layout_sidebar_icons');
		if (!el[0] || el.find('li.is-enabled').length == 0) {
			this.anyAppsSidebar = false;
			this.fragmentElement.removeClass('with-apps-sidebar with-docked-apps-sidebar');
			this.fragmentElement.triggerHandler('onNoAppsSidebar');
			if (this.updateAppSidebarUi) {
				this.updateAppSidebarUi();
			}
		} else {
			this.anyAppsSidebar = true;
			this.fragmentElement.addClass('with-apps-sidebar');
			this.fragmentElement.triggerHandler('onAppsSidebar');
			this._initAppsSidebar();
		}
	},

	_initAppsSidebar: function() {
		if (this._hasInitAppsSidebar) return;

		var self = this;

		var sidebarEl   = this.getEl('layout_sidebar');
		var layoutEl    = this.getEl('layout_content');
		var iconsEl     = this.getEl('layout_sidebar_icons');
		var sizer       = this.getEl('layout_sidebar_sizer');
		var isOver      = false;
		var isPlaceOver = false;
		var isSizerOver = false;
		var isSizing     = false;
		var isClosing   = false;
		var openTimeout = null;
		var outTimeout  = null;
		var initialUpdateDone = false;

		var sizerCalcLeft = function() {
			var l = parseInt(self.fragmentElement.width()) - parseInt(DeskPRO_Window.appsSidebar.width);
			return l;
		};

		var open = function(openNow) {
			if (DeskPRO_Window.appsSidebar.visible || !self.anyAppsSidebar) return;

			self.fragmentElement.addClass('with-apps-sidebar-overlay');

			if (openNow) {
				sidebarEl.stop().css('right', 0).show();
				sizer.css('left', sizerCalcLeft()).show();
			} else {
				sidebarEl.css('right', -DeskPRO_Window.appsSidebar.width).css('width', DeskPRO_Window.appsSidebar.width).show();
				sidebarEl.stop().animate({right: 0}, {
					duration: 350,
					complete: function () {
						sizer.css('left', sizerCalcLeft()).show();
						if (!isOver && !isPlaceOver && !outTimeout && !isSizerOver && !isSizing) {
							outTimeout = window.setTimeout(function () {
								outTimeout = null;
								if (!isOver && !isPlaceOver && !isSizerOver && !isSizing) {
									close();
								}
							}, 380);
						}
					}
				});
			}
		};

		var close = function() {
			if (DeskPRO_Window.appsSidebar.visible || !self.anyAppsSidebar) return;
			isClosing = true;
			sidebarEl.stop().animate({right: -DeskPRO_Window.appsSidebar.width}, {
				duration: 350,
				complete: function() {
					isClosing = false;
					sidebarEl.hide();
					sizer.hide();
					self.fragmentElement.removeClass('with-apps-sidebar-overlay');
				}
			})
		};

		var startCloseTimeout = function() {
			if (!outTimeout) {
				outTimeout = window.setTimeout(function() {
					outTimeout = null;
					if (!isOver && !isPlaceOver && !isSizerOver && !isSizing) {
						close();
					}
				}, 380);
			}
		};

		var cancelCloseTimeout = function() {
			if (outTimeout) {
				window.clearTimeout(outTimeout);
				outTimeout = null;
			}
			if (isClosing) {
				isClosing = false;
				sidebarEl.stop().animate({right: 0}, {duration: 150});
				sizer.css('left', sizerCalcLeft()).show();
			}
		};

		var togglePin = function() {
			if (DeskPRO_Window.appsSidebar.visible) {
				closePin();
			} else {
				openPin();
			}
		};

		var openPin = function() {
			if (outTimeout) {
				window.clearTimeout(outTimeout);
				outTimeout = null;
			}
			if (openTimeout) {
				window.clearTimeout(openTimeout);
				openTimeout = null;
			}
			sidebarEl.stop().css('right', 0).css('width', DeskPRO_Window.appsSidebar.width).show();
			DeskPRO_Window.appsSidebar.visible = true;
			sizer.css('left', sizerCalcLeft()).show();
			layoutEl.css('right', DeskPRO_Window.appsSidebar.width);
			self.fragmentElement.removeClass('with-apps-sidebar-overlay');
			self.fragmentElement.addClass('with-docked-apps-sidebar');

			if (Modernizr.localstorage) {
				localStorage['apps_sidebar_state'] = 'open';
			}
		};

		var closePin = function() {
			if (outTimeout) {
				window.clearTimeout(outTimeout);
				outTimeout = null;
			}
			if (openTimeout) {
				window.clearTimeout(openTimeout);
				openTimeout = null;
			}
			sidebarEl.stop().hide();
			DeskPRO_Window.appsSidebar.visible = false;
			layoutEl.css('right', 0);
			self.fragmentElement.removeClass('with-apps-sidebar-overlay');
			self.fragmentElement.removeClass('with-docked-apps-sidebar');

			if (Modernizr.localstorage) {
				localStorage['apps_sidebar_state'] = 'closed';
			}
		};

		var updateUi = function() {
			if (DeskPRO_Window.appsSidebar.visible && self.anyAppsSidebar) {
				sidebarEl.stop().css('right', 0).css('width', DeskPRO_Window.appsSidebar.width).show();
				sizer.css('left', sizerCalcLeft()).show();
				layoutEl.css('right', DeskPRO_Window.appsSidebar.width);
				self.fragmentElement.removeClass('with-apps-sidebar-overlay');
				self.fragmentElement.addClass('with-docked-apps-sidebar');
			} else {
				sidebarEl.stop().hide();
				layoutEl.css('right', 0);
				self.fragmentElement.removeClass('with-apps-sidebar-overlay');
				self.fragmentElement.removeClass('with-docked-apps-sidebar');
			}
		};

		this.updateAppSidebarUi = updateUi;
		this.addEvent('activate', function(){
			updateUi();
		});
		updateUi();

		sidebarEl.on('mouseover', function() {
			isOver = true;
			cancelCloseTimeout();
		}).on('mouseout', function() {
			isOver = false;
			startCloseTimeout();
		});

		iconsEl.on('click', function(ev) {
			ev.stopPropagation();
			ev.stopImmediatePropagation();
			ev.preventDefault();
			open(true);
		});
		sidebarEl.find('.pin-btn').on('click', function(ev) {
			ev.stopPropagation();
			ev.stopImmediatePropagation();
			ev.preventDefault();
			togglePin();
		});

		iconsEl.on('mouseover', function() {
			isPlaceOver = true;
			openTimeout = window.setTimeout(function() {
				openTimeout = null;
				if (isPlaceOver) {
					open();
				}
			}, 250);
		}).on('mouseout', function() {
			isPlaceOver = false;
			if (openTimeout) {
				window.clearTimeout(openTimeout);
				openTimeout = null;
			}
		});

		sizer.on('mouseover', function() {
			isSizerOver = true;
		}).on('mouseout', function() {
			isSizerOver = false;
			startCloseTimeout();
		}).draggable({
			axis: 'x'
		}).on('dragstart', function() {
			sizer.addClass('dragging');
			isSizing = true;
		}).on('dragstop', function() {
			sizer.removeClass('dragging');
			isSizing = false;
			var w = self.fragmentElement.width() - sizer.position().left
			DeskPRO_Window.appsSidebar.width = w;
			sidebarEl.css('width', w);
			if (DeskPRO_Window.appsSidebar.visible) {
				layoutEl.css('right', w);
			}

			if (Modernizr.localstorage) {
				localStorage['apps_sidebar_width'] = w;
			}
		});
	},

	destroy: function() {

	}
});
