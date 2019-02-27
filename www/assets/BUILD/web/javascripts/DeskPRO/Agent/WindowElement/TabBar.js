Orb.createNamespace('DeskPRO.Agent.WindowElement');

/**
 * The tabbar handles adding and removing tabs in the right pane of the window.
 */
DeskPRO.Agent.WindowElement.TabBar = new Orb.Class({

  Implements: [Orb.Util.Events, Orb.Util.Options],

  initialize: function (options) {
    this.options = {
      /**
       * The element that contains the tabs. This is the pane or wrapper element that
       * contains the actual list. It must include a list with class '.dp-tab-list'
       */
      tabPane: null,

      /**
       * The element the append body pages to
       */
      bodyPane: null,

      /**
       * when enabled, init is run when the html is entered into the dom
       * when disabled, init is run when tab is focused for first time
       */
      initOnRender: false
    };

    this.setOptions(options);
    this.preInitWait = 0;
    this.preInitTabs = _.debounce(this.preInitTabsNow.bind(this), 750);
    this._checkOpenedItemsDebounce = _.debounce(this._checkOpenedItems.bind(this), 750);

    this.tabPane = $(this.options.tabPane);
    this.tabList = this.tabPane.find('ul.dp-tab-list').first();
    this.bodyPane = $(this.options.bodyPane);
    this.menuBtn = $(this.options.menuBtn);
    this.active = null;
    this.initOnRender = this.options.initOnRender || false;

    this.tabCount = 0;

    this.showListIfNoTabs = _.debounce((function () {
      var last_tab_id = Object.keys(this.tabs).getLast();
      if (!last_tab_id) {
        this.$timeout((function () {
          this.$scope.showList();
        }).bind(this));
      }
    }).bind(this), 400);

    this.tabs = {};
    this._tabs = [];
    this.currentTabId = null;
    this.initScope();

    this._closeGoNextTabOldIdx = null;
    this._closeGoNextTab = _.debounce((function () {
      if (this._closeGoNextTabOldIdx === null) return;
      var oldTabIdx = this._closeGoNextTabOldIdx;
      this._closeGoNextTabOldIdx = null;

      this.activateNextTab(oldTabIdx);
    }).bind(this), 30);

    this.tabBarOverflow = new DeskPRO.Agent.WindowElement.TabBarOverflow();
  },

  activateNextTab: function (oldTabIdx) {
    if (!oldTabIdx) {
      oldTabIdx = this._tabs.indexOf(this.getActiveTab());
    }

    // Go to the next tab
    if (oldTabIdx != -1 && this._tabs[oldTabIdx]) {
      this.activateTab(this._tabs[oldTabIdx]);

      // Was last, so go to the previous
    } else if (oldTabIdx != -1 && this._tabs[oldTabIdx - 1]) {
      this.activateTab(this._tabs[oldTabIdx - 1]);

      // Otherwise go to the last
    } else {
      var last_tab_id = Object.keys(this.tabs).getLast();
      if (last_tab_id) {
        this.activateTabById(last_tab_id);
      } else {
        // If list view isnt active, then after a small timeout
        // make it visiable.
        // The timeout is in case we have other routines that auto-open
        // a new tab (e.g., after ticket reply)
        this.showListIfNoTabs();
      }
    }

    this.$scope.$safeApply();
  },

  preInitTabsNow: function () {
    Object.each(this.tabs, (function (data) {
      var preInit = (function () {
        this.preInitWait--;
        if (this.preInitWait < 0) {
          this.preInitWait = 0;
        }

        if (data.isInited) {
          return;
        }
        data.isInited = true;

        if (data.callback_render !== undefined) {
          data.callback_render(data.wrapper);
        }
      }).bind(this);
      this.preInitWait++;

      window.requestIdleCallback ?
        window.requestIdleCallback(preInit, {timeout: 5000}) :
        window.setTimeout(preInit, this.preInitWait * 100);
    }).bind(this));
  },

  enableInitOnRender: function () {
    this.initOnRender = true;
  },

  disableInitOnRender: function () {
    this.initOnRender = true;
  },

  initScope: function () {
    var self = this;
    this.$scope = DeskPRO_Window.$scope;
    this.$timeout = DeskPRO_Window.$timeout;
    this.$scope.tabs = this._tabs;
    this.$scope.contextMenuTab = null;
    this.$scope.tabClick = function ($event, tab) {
      self._tabStripClick($event, tab);
    };
    this.$scope.tabHistory = [];

    this.$scope.context = function (event) {
			var tab = null;
			if (event.target.tagName === 'A') {
				var li = event.target.parentElement;
				tab = self._tabs.find(function (a) { return a.id === li.dataset.tabId});
			}
      self._filterTabHistory();
      self.$scope.contextTab = tab;
    };

    var getCloseAll = function () {
      var tabs = [];
      self._tabs.each(function (tab) {
        if (tab.locked) {
          return;
        }

        tabs.push(tab);
      });

      return tabs;
    };

    this.$scope.showCloseAll = function () {
      return getCloseAll().length > 0;
    };

    this.$scope.closeAll = function () {
      getCloseAll().forEach(function (tab) {
        self.removeTab(tab);
      });
    };

    var getCloseOthers = function () {
      var tabs = [], active = self.getActiveTab();
      self._tabs.forEach(function (tab) {
        if (tab === active || tab.locked) {
          return;
        }

        tabs.push(tab);
      });

      return tabs;
    };

    this.$scope.showCloseOthers = function () {
      return getCloseOthers().length > 0;
    };

    this.$scope.closeOthers = function () {
      getCloseOthers().forEach(function (tab) {
        self.removeTab(tab);
      });
    };

    this.$scope.closeCurrent = function () {
      self.$scope.contextTab && self.removeTab(self.$scope.contextTab);
    };

    this.$scope.reopenTab = function (tab) {
      window.DeskPRO_Window.runPageRoute(tab.route, {noToggle: true, focus: true});
    };

    this.$scope.$watch('tabs', function (tabs) {
      self._filterTabHistory();
    });

    this.$scope.$watch('listItems', function () {
      self._checkOpenedItems();
    }, true);
  },

  _filterTabHistory: function () {
    var tabRoutes = {}, i, t;
    var tabHistory = [];
    for (i = 0; i < this.$scope.tabs.length; i++) {
      t = this.$scope.tabs[i];
      if (t.page && t.page.meta && t.page.meta.routeUrl) {
        tabRoutes['page:' + t.page.meta.routeUrl] = true;
      }
    }
    for (i = 0; i < this.$scope.tabHistory.length; i++) {
      if (!tabRoutes[this.$scope.tabHistory[i].route]) {
        tabHistory.push(this.$scope.tabHistory[i]);
        tabRoutes[this.$scope.tabHistory[i].route] = true;
      }
    }
    this.$scope.tabHistory = tabHistory;
  },

  _checkOpenedItems: function () {
    var self = this;

    this.$scope.listItems.forEach(function (item) {
      item.open = false;
      for (var i = 0; i < self._tabs.length; i++) {
        var tab = self._tabs[i];
        if (tab.page && tab.page.meta.pageIdentity === item.identity) {
          item.open = true;
          return;
        }
      }
    });
  },

  rescanTitles: function () {
    var i, tab;
    for (i = 0; i < this._tabs.length; i++) {
      tab = this._tabs[i];
      if (tab.page) {
        var title = tab.page.getMetaData('title', null);
        var classId = tab.page.getMetaData('tabClassId', '');

        if (title) {
          tab.title = title;
        }
        if (classId) {
          classId = classId + '';
        }
        if (classId && classId.length) {
          tab.classId = classId;
        }
      }
    }
  },

  //##################################################################################################################
  // Methods to fetch tabs
  //##################################################################################################################

  /**
   * Get the active tab ID
   *
   * @return {String}
   */
  getActiveTabId: function () {
    return this.currentTabId;
  },


  /**
   * Get the active tab object
   *
   * @return {Object}
   */
  getActiveTab: function () {
    return this.getTab(this.currentTabId);
  },


  /**
   * Get a tab by its id
   *
   * @param {String} id
   * @return {Object}
   */
  getTab: function (id) {
    if (this.tabs[id] == undefined) {
      return null;
    }

    return this.tabs[id];
  },


  /**
   * Get all tabs
   *
   * @return {Object}
   */
  getTabs: function () {
    return this.tabs;
  },


  /**
   * Get an array of all tab ids
   *
   * @return {Array}
   */
  getTabIds: function () {
    return Object.keys(this.tabs);
  },


  /**
   * Find a tab by pages fragment.
   *
   * @param {String} fragment
   * @return {Object}
   */
  findTabByFragment: function (fragment) {
    var retTab = null;

    Object.each(this.tabs, function (tab) {
      if (tab.page && tab.page.getMetaData('url_fragment') == fragment) {
        retTab = tab;
        return false;
      }
    });

    return retTab;
  },


  /**
   * Find a tab by its pages route url
   *
   * @param {String} routeUrl
   * @return {String}
   */
  findTabByRouteUrl: function (routeUrl) {
    var retTab = null;

    Object.each(this.tabs, function (tab) {
      if (tab.page.getMetaData('routeUrl') == routeUrl) {
        retTab = tab;
        return false;
      }
    });

    return retTab;
  },


  //##################################################################################################################
  // Adding a removing tabs
  //##################################################################################################################

  /**
   * Add a page to the tabbar
   *
   * @param {Object} page
   */
  addTab: function (page) {

    DeskPRO_Window.backToAgent();
    var self = this;

    this.isAdding = true;

    var id = Orb.uuid();
    page.meta.tabId = id;

    var data = {};
    data.id = id;
    data.page = page;
    data.classId = page.getMetaData('tabClassId', '');
    data.title = page.getMetaData('title', 'Untitled');
    data.initOnRender = this.initOnRender;
    data.callback_render = function (container) {
      container = $(container);
      page.fireEvent('render', [container.first(), id]);
    };
    data.callback_remove_content = function (data, container) {
      if (data.isInited) {
        page.fireEvent('destroy');
        page.destroyEvents();
      }
    };
    data.callback_activate = function () {
      page.fireEvent('activate');
    };
    data.callback_deactivate = function () {
      page.fireEvent('deactivate');
    };

    data.isInited = false;

    if (!this.tabs[id]) {
      this.tabCount++;
      $('body').removeClass('without-tabs').addClass('with-tabs');
    }
    this.tabs[id] = data;

    //----------
    // Render content to dom
    //----------

    // The tab content
    data.wrapperId = 'tabcontent_' + id;

    if (page.meta.existingWrapper) {
      data.wrapper = page.meta.existingWrapper;
      data.wrapper.attr('id', data.wrapperId);
      data.wrapper.attr('class', 'tabViewDetailContent');
      data.wrapper.css('display', 'none');
      data.wrapper.appendTo(this.bodyPane);
    } else {
      var preparedOutput = DeskPRO_Window.prepareWidgetedHtml(page.getHtml());

      data.wrapper = $('<div id="' + data.wrapperId + '" class="tabViewDetailContent" style="display: none"></div>');
      data.wrapper.html(preparedOutput.html);

      if (page && page.prepareWrapper) {
        page.prepareWrapper(data.wrapper);
      }

      data.wrapper.appendTo(this.bodyPane);
      DeskPRO_Window.runWidgetedJs(data.page, preparedOutput.jsSource, preparedOutput.jsInline);
    }

    //----------
    // Render tab button
    //----------

    data.tabBtnId = 'tabbtn_' + id;
    data.tabType = null;

    if (page) {
      if (page.TYPENAME_FOR) {
        data.tabType = page.TYPENAME_FOR;
      } else if (page.TYPENAME) {
        data.tabType = page.TYPENAME;
      }
    }

    var wasActive = false;
    var otherTab = null;
    var placeTab = this.getActiveTab();

    if (data.page && data.page.meta.tabPlaceholderId) {
      otherTab = this.getTab(data.page.meta.tabPlaceholderId);
    }

    // We may have had a placeholder, in which case we want to place
    // the new tab where the old one was while also removing the placeholder
    // content in the body pane

    // insert new tab after placeholder that will be removed in a moment
    if (otherTab && this._tabs.indexOf(otherTab) < this._tabs.length - 1) {
      this._tabs.splice(this._tabs.indexOf(otherTab) + 1, 0, data);

      // insert new tab after the currently selected tab
    } else if (placeTab && this._tabs.indexOf(placeTab) < this._tabs.length - 1) {
      this._tabs.splice(this._tabs.indexOf(placeTab) + 1, 0, data);

      // just push it on to the end
    } else {
      this._tabs.push(data);
    }

    if (otherTab) {
      if (this.currentTabId == otherTab.id) {
        wasActive = true;
        this.currentTabId = null;
      }

      if (otherTab.page) {
        otherTab.page.HAS_REAL_TAB = true;
      }
      this.removeTab(otherTab, true);
    }

    this._checkOpenedItems();

    //----------
    // Just about done
    //----------

    if (!data.isInited && data.initOnRender && (!otherTab || otherTab.initOnRender)) {
      this.preInitTabs();
    }

    this.fireEvent('addTab', [data, this]);

    if (!this.currentTabId || wasActive) {
      this.activateTabById(id);
    } else {
      DeskPRO_Window.updateWindowUrlFragment();
    }

    this.isAdding = false;

    this.updateTabBarOverflow();

    return id;
  },

  updateTabBarOverflow: function () {
    var self = this;
    this.$timeout(function updateTabBarOverflow() {
      if (self.tabBarOverflow) {
        self.tabBarOverflow.update();
      }
    });
  },

  /**
   * Like addTab except the tab is marked as "loading"
   *
   * @param url
   * @param routeData
   */
  addTabPlaceholder: function (url, routeData) {
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

    var existTab = null;
    if (page.meta && page.meta.routeUrl) {
      existTab = this.findTabByRouteUrl(page.meta.routeUrl);
    }

    var id = this.addTab(page);
    if ((existTab && this.getActiveTab() === existTab) || page.meta.routeData.isBackgroundLoad) {
      // nothing, dont focus it
    } else {
      this.activateTabById(id);
    }

    if (routeData.tabLoad) {
      routeData.tabLoad();
    }

    return id;
  },


  /**
   * Activate a tab in the tabbar
   *
   * @param {Object} tab
   */
  activateTab: function (tab) {

    if (!tab) {
      return;
    }

    var id = tab.id;

    // Already the current tab
    if (id == this.currentTabId) {
      return;
    }

    this.isActivating = true;

    if (this.currentTabId) {
      this.deactivateCurrentTab();
    }

    var data = this.tabs[id];
    if (!data || !data.wrapper) {
      this.removeTab(tab, true);
    }
    var wrapper = data.wrapper.show();

    if (!data.isInited) {
      data.isInited = true;

      if (data.callback_render !== undefined) {
        data.callback_render(wrapper);
      }
    }

    if (data.callback_activate !== undefined) {
      data.callback_activate(data, wrapper, this);
    }

    tab.isAlerting = false;

    this.currentTabId = id;

    this.fireEvent('activateTab', [data, wrapper, this]);

    this.isActivating = false;
    data.isActive = true;
    DeskPRO_Window.updateWindowUrlFragment();
  },


  /**
   * Deactivate the currently selected tab
   */
  deactivateCurrentTab: function () {

    if (!this.currentTabId) {
      return;
    }

    var data = this.tabs[this.currentTabId];
    data.isActive = false;

    console.log('Hiding tab content: %o, id: %s', this.currentTabId, data.wrapperId);
    $('#' + data.wrapperId).hide();

    if (data.callback_deactivate !== undefined) {
      data.callback_deactivate(data, $('#' + data.wrapperId), this);
    }

    this.fireEvent('deactivateTab', [data]);

    this.currentTabId = null;
  },

  /**
   * Determines if a tab is visible in relation to scrolling.
   *
   * @param tab
   */
  isTabVisible: function (tab) {
    var $el = $(tab.tabBtnId);
    if (!$el || !$el[0]) return;
    var left = $el.position().left;

    // Attempt to ignore margin and border. Lets hope they're the same on both sides.
    var guess_slack = Math.round(($el.outerWidth() - $el.innerWidth()) / 2);
    var right = left + $el.innerWidth() + guess_slack;

    var bounds = this.tabBarOverflow ? this.tabBarOverflow.getBounds() : {};

    return !(right < bounds.left || left > bounds.right);
  },

  /**
   * Remove a tab
   *
   * @param {Object} tab
   * @param {Boolean} [silent]
   */
  removeTab: function (tab, silent) {

    var self = this;
    var id = tab.id;
    var wasActive = false;
    var oldTabIdx = this._tabs.indexOf(tab);

    if (this.currentTabId == id) {
      wasActive = true;
      if (!silent) {
        this.deactivateCurrentTab()
      }

      this.currentTabId = null;
    }

    var data = this.tabs[id];
    if (!data) {
      return;
    }
    delete this.tabs[id];
    this.tabCount--;
    if (this.tabCount <= 0) {
      $('body').addClass('without-tabs').removeClass('with-tabs');
    }
    this._tabs.splice(this._tabs.indexOf(tab), 1);
    if (tab === this.$scope.contextTab) {
      this.$scope.contextTab = null;
    }

    if (!silent) {
      if (data.tabBtn) {
        data.tabBtn.remove();
        data.tabBtn = null;
      }
      if (data.tabBtn2) {
        data.tabBtn2.remove();
        data.tabBtn2 = null;
      }
    }

    if (data.page) {
      if (data.page.meta.routeData && data.page.meta.routeData.xhr) {
        data.page.meta.routeData.xhr.abort();
      }

      if (data.page.meta.routeData && data.page.meta.routeData.dataUnload) {
        data.page.meta.routeData.dataUnload();
      }
    }

    if (!silent) {
      this.fireEvent('removeTab', [data, this]);

      if (wasActive) {
        this._closeGoNextTabOldIdx = oldTabIdx;
        this._closeGoNextTab();
      }
    }

    DeskPRO_Window.updateWindowUrlFragment();
    if (!silent && tab.page && tab.page.meta.routeUrl && !tab.page.LOADING_TYPENAME) {
      this.$scope.tabHistory.push({
        title: tab.title,
        route: 'page:' + tab.page.meta.routeUrl
      });
      this._filterTabHistory();
      while (this.$scope.tabHistory.length > 5) {
        this.$scope.tabHistory.shift();
      }
    }

    if (this.tabBarOverflow) {
      this.tabBarOverflow.debouncedUpdate();
    }

    this.$timeout(function () {
      self.tabBarOverflow.debouncedUpdate();
    });

    if (data.wrapper) {
      data.wrapper.hide();
    }

    var doRemove = function () {
      if (typeof data.callback_remove_content !== 'undefined') {
        data.callback_remove_content(data, $('#' + data.wrapperId), this);
      }

      if (data.page.destroyEvents) {
        data.page.destroyEvents();
      }

      // checks if the page wrapper still exists and is still attached
      if (data.page.wrapper && data.page.wrapper.parent()[0]) {
        data.page.wrapper.empty();
        data.page.wrapper.removeData();
        data.page.wrapper.off();
        data.page.wrapper.remove();
        data.page.wrapper = null;
      }

      if (data.wrapper && data.wrapper.parent()[0]) {
        data.wrapper.empty();
        data.wrapper.removeData();
        data.wrapper.off();
        data.wrapper.remove();
        data.wrapper = null;
      }

      data.callback_render = null;
      data.callback_remove_content = null;
      data.callback_activate = null;
      data.callback_deactivate = null;
      data.page = null;

      self._checkOpenedItemsDebounce();
    };

    window.setTimeout(function() {
      window.requestIdleCallback ?
        window.requestIdleCallback(doRemove, {timeout: 25000}) :
        doRemove();
    }, 3500);
  },


  /**
   * Remove a tab via id
   *
   * @param {String} id
   */
  removeTabById: function (id) {
    var tab = this.getTab(id);
    if (!tab) {
      console.log("Cannot remove, unknown tab %s", id);
      console.trace();
      return null;
    }
    this.removeTab(tab);
  },


  /**
   * Activate a tab by id
   *
   * @param {String} id
   */
  activateTabById: function (id) {
    var tab = this.getTab(id);
    if (!tab) {
      console.log("Cannot activate, unknown tab %s", id);
    }
    this.activateTab(tab);
  },

  /**
   * Activate a tab by id
   *
   * @param {String} id
   */
  tabToFrontTabById: function (id, noalert) {
    var tab = this.getTab(id);

    if (!tab) {
      console.log("Cannot activate, unknown tab %s", id);
    }

    var otherTab = null;
    if (tab.page && tab.page.meta.tabPlaceholderId) {
      otherTab = this.getTab(tab.page.meta.tabPlaceholderId);
    }

    this._tabs.splice(this._tabs.indexOf(tab), 1);

    if (otherTab && otherTab != tab) {
      if (this.currentTabId == otherTab.id) {
        this.currentTabId = null;
      }

      this._tabs.indexOf(otherTab) < this._tabs.length - 1
        ? this._tabs.splice(this._tabs.indexOf(otherTab) + 1, 0, tab)
        : this._tabs.push(tab);

      this.removeTab(otherTab, true);
    } else {
      this._tabs.unshift(tab);
    }

    if (this.tabBarOverflow) {
      this.tabBarOverflow.resetScroll();
    }

    if (!noalert) {
      $(tab.tabBtnId).effect("pulsate", {times: 4}, 500);
    }
  },

  //##################################################################################################################
  // Tab functionality
  //##################################################################################################################

  alertTab: function (tab) {
    var el = $(tab.tabBtnId);
    if (tab.isActive || el.isAlerting) return;

    this.$scope.$safeApply(function () {
      tab.isAlerting = true;
    });
  },

  clearAlertTab: function (tab) {
    this.$scope.$safeApply(function () {
      tab.isAlerting = false;
    });
  },

  _alertTabDoHighlight: function (el) {
    el.toggleClass('alert-highlight');
  },

  lockTab: function (tab) {
    this.$scope.$safeApply(function () {
      tab.locked = true;
    });
  },

  unlockTab: function (tab) {
    this.$scope.$safeApply(function () {
      tab.locked = false;
    });
  },

  //##################################################################################################################
  // Handling events
  //##################################################################################################################

  _tabStripClick: function (event, tab) {

    if (this.cancelClickActivate) {
      this.cancelClickActivate = false;
      return;
    }

    this.cancelClickActivate = true;

    var el_click = $(event.target);

    // If the clicked thing was the close button, or if its a middle-click...
    if (el_click.is('.close') || event.which == 2 || event.isDbl) {
      if (!tab) {
        return;
      }

      console.log('close');
      if (tab.page && tab.page.fireEvent) {
        event.deskpro = {cancelClose: false};
        tab.page.fireEvent('closeTab', [event, tab]);

        if (event.deskpro.cancelClose) {
          this.cancelClickActivate = false;
          return;
        }
      }

      tab.isCloseClick = true;
      this.removeTabById(tab.id);
      tab.isCloseClick = false;

      this.cancelClickActivate = false;

      return;
    }

    // Otherwise activate the tab
    this.activateTabById(tab.id);

    DeskPRO_Window.$scope.showTabs();

    this.cancelClickActivate = false;
  }
});
