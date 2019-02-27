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
    var runner = (function() {
      if (this.wrapper) {
        if (!this.scrollHandlers) {
          this.scrollHandlers = this.wrapper.find('div.with-scroll-handler');
        }
        for (x = 0; x < this.scrollHandlers.length; x++) {
          var sh = $(this.scrollHandlers[x]).data('scroll_handler');
          if (sh && sh.updateSize) {
            sh.updateSize();
          }
        }
        ;
      }

      this.fireEvent('updateUi');
    }).bind(this);

    if (window.requestAnimationFrame) {
      window.requestAnimationFrame(runner);
    } else {
      runner();
    }
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

    this._hasInitAppsSidebar = false;

    this.resizerInterval = window.setInterval(function() {
      if (self.IS_ACTIVE) self.updateUi();
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

      DeskPRO_Window.initInterfaceServices(wrapper);

      if (!this.noDeleteHtmlString) {
        this.html = null;
      }

      this.initPage(wrapper);
      if (window.requestAnimationFrame) {
        window.requestAnimationFrame(function() {
          self.initApps();
        });
      } else {
        this.initApps();
      }

      DeskPRO_Window.TabBar.rescanTitles();
      DeskPRO_Window.getMessageBroker().sendMessage('agent.ui.tabinit.' + this.TYPENAME, this);

    }, this);

    // Standard hook methods
    this.addEvent('activate', this.activate);
    this.addEvent('deactivate', this.deactivate);
    this.addEvent('destroy', function(){
      DeskPRO_Window.getMessageBroker().sendMessage('agent.ui.tabbeforedestroy.' + this.TYPENAME, this);
    }, this);
    this.addEvent('destroy', function(){
      if (self.wrapper) {
        $('.tipped', self.wrapper).remove();
      }
    });
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

      if (this.scrollerHandler) {
        this.scrollerHandler.destroy();
        this.scrollerHandler = null;
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
          var select2 = $(this).data('select2');
          if (select2 && select2.opts.element) {
            select2.opts.element.removeData().off();
            select2.opts = null;
          }
        });
        self.wrapper.find('textarea.with-redactor').each(function() {
          var obj = $(this).getObject();
          if (obj) {
            $(this).getObject().destroy();
          }
        });

        self.wrapper.data('page-fragment', null);
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
          h = $(this).data('scroll_handler');
          if (h) {
            h.destroy();
          }
        });
      }
      // TabBar removes wrapper when its rendered in a tab
      if (!self.meta || !self.meta.tabId) {
        window.setTimeout(function() {
          if (self.wrapper) {
            self.wrapper.empty();
            self.wrapper.removeData();
            self.wrapper.off();
            self.wrapper = null;
          }
        }, 10000);
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

  togglePinAppsColumn: function()
  {
    if (DeskPRO_Window.appsSidebar.pinned) {
      this.unpinAppsSidebar();
      this.collapseAppsSidebar();
    } else {
      this.pinAppsSidebar();
      this.expandAppsSidebar();
    }
  },

  updateAppsSidebar: function() {
      this.wrapper.addClass('with-apps-sidebar');
      this.wrapper.triggerHandler('onAppsSidebar');
      this._initAppsSidebar();
  },

  expandAppsSidebar: function()
  {
    this.wrapper.removeClass('with-apps-sidebar-collapsed');
    this.wrapper.addClass('with-apps-sidebar-expanded');
    DeskPRO_Window.appsSidebar.expanded = true;
    if (Modernizr.localstorage) {
      localStorage['apps_sidebar_state'] = JSON.stringify(DeskPRO_Window.appsSidebar);
    }
  },

  collapseAppsSidebar: function()
  {
    this.wrapper.removeClass('with-apps-sidebar-expanded');
    this.wrapper.addClass('with-apps-sidebar-collapsed');
    DeskPRO_Window.appsSidebar.expanded = false;
    if (Modernizr.localstorage) {
      localStorage['apps_sidebar_state'] = JSON.stringify(DeskPRO_Window.appsSidebar);
    }
  },

  pinAppsSidebar: function()
  {
    DeskPRO_Window.appsSidebar.pinned = true;
    DeskPRO_Window.appsSidebar.expanded = true;
    if (Modernizr.localstorage) {
      localStorage['apps_sidebar_state'] = JSON.stringify(DeskPRO_Window.appsSidebar);
    }

    self.wrapper.addClass('with-apps-sidebar-pinned');

    var sidebarEl   = this.getEl('layout_sidebar');
    sidebarEl.addClass('sidebar-pinned');
  },

  unpinAppsSidebar: function()
  {
    DeskPRO_Window.appsSidebar.pinned = false;
    DeskPRO_Window.appsSidebar.expanded = false;
    if (Modernizr.localstorage) {
      localStorage['apps_sidebar_state'] = JSON.stringify(DeskPRO_Window.appsSidebar);
    }

    self.wrapper.removeClass('with-apps-sidebar-pinned');

    var sidebarEl   = this.getEl('layout_sidebar');
    sidebarEl.removeClass('sidebar-pinned');
  },

  _initAppsSidebar: function() {
    if (this._hasInitAppsSidebar) return;
    this._hasInitAppsSidebar = true;

    DeskPRO_Window.getMessageBroker().addMessageListener(['apps-column.togglePin', this.pageUid].join('.'), this.togglePinAppsColumn, this);
    DeskPRO_Window.getMessageBroker().addMessageListener(['apps-column.expand', this.pageUid].join('.'),  this.expandAppsSidebar, this);
    DeskPRO_Window.getMessageBroker().addMessageListener(['apps-column.collapse', this.pageUid].join('.'), this.collapseAppsSidebar, this);

    this.addEvent('destroy', function(){
      DeskPRO_Window.getMessageBroker().removeMessageListener(['apps-column.togglePin', this.pageUid].join('.'), this.togglePinAppsColumn, this);
      DeskPRO_Window.getMessageBroker().removeMessageListener(['apps-column.expand', this.pageUid].join('.'),  this.expandAppsSidebar, this);
      DeskPRO_Window.getMessageBroker().removeMessageListener(['apps-column.collapse', this.pageUid].join('.'), this.collapseAppsSidebar, this);

    });


    var self = this;

    try {
      DeskPRO_Window.appsSidebar = JSON.parse(localStorage['apps_sidebar_state']);
    } catch (e) {}

    if (typeof DeskPRO_Window.appsSidebar !== 'object') {
      DeskPRO_Window.appsSidebar.pinned = false;
      DeskPRO_Window.appsSidebar.expanded = false;
    }

    var updateUi = function() {
      if (DeskPRO_Window.appsSidebar.expanded) {
        self.pinAppsSidebar();
        self.expandAppsSidebar();
      } else {
        self.unpinAppsSidebar();
        self.collapseAppsSidebar();
      }
    };

    // updateAppSidebarUi apparently is required some place
    this.updateAppSidebarUi = updateUi;
    this.addEvent('activate', function(){
      updateUi();
    });
    updateUi();
  },

  destroy: function() {

  }
});
