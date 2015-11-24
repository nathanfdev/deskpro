class Agent_DeskPRO_Window {
  constructor() {

    this.hashHandling = true;
    this.onloadStack = [];
    this.dismissAlertQueue = [];
    this.routePrefixes = {};

    this.messageChanneler = null;
    this.poller = null;

    this.sections = {};
    this.openSection = null;

    this.listPage = null;

    this.innerLayout = null;

    this._alertOverlay = null;
    this._confirmOverlay = null;

    this.loadingIndicatorEl = null;
    this.loadingIndicatorCount = 0;
    this.ajaxErrorOverlay = null;

    this.cancelHashLoad = 0;
    this.activeListNav = null;
    this.activityTime = new Date();
    this.isMobile = false;

    this.appsSidebar = {
      visible: false,
      width: 350
    };


    this.agentNotifyListShown = false;

    this.paneVis = {
      source: true,
      list: true,
      tabs: true
    };

    this.paneVisBit = {
      source: 1,
      list: 2,
      tabs: 4
    };

    this.util = {
      modCountEl: function(el, op, num) {
        console.trace();
      },

      getPlainTpl: function(el) {
        console.trace();
      },

      showSavePuff: function(overEl) {
        console.trace();
      },

      ajaxWithClientMessages: function(options) {
        console.trace();
      },

      slugify: function(str) {
        str = str.replace(/[^a-zA-Z0-9\-]/g, '-');
        str = str.replace(/\-{2,}/g, '-');
        str = str.replace(/^\-/, '');
        str = str.replace(/\-$/, '');

        return str;
      },

      linkUrls: function(string) {
        string = string||'';
        string = Orb.linkUrls(string);
        string = string.replace(/<a /g, '<a target="_blank" ');
        return string;
      },

      dpCheckbox: function(input) {
        console.trace();
      },

      fileupload: function(el, options) {
        console.trace();
      },

      updateUserEmailAddressDisplay: function(person_id, email) {
        console.trace();
      },

      reloadInterface: function() {
        window.location.reload(false);
      }
    };
  }

  initPage() {
    this._initLayout();
    this._initWindowInterface();
    this._initBasic();
    this._initRoutes();
    this._initSections();
    this._initInterfaceServices();

    this.ticketSnippetDriver = new DeskPRO.Agent.TextSnippetAjaxDriver('tickets');
    this.chatSnippetDriver   = new DeskPRO.Agent.TextSnippetAjaxDriver('chat');

    window.DeskPRO_FragmentRouter = {
      baseUrl: '',
      setBaseUrl: function(x) { this.baseUrl = x; },
      hasFragment: function() { return false; },
      getFragmentPattern: function() { return ''; },
      getFragmentType: function() { return ''; },
      getUrl: function() { return ''; },
      getUrlNamedArgs: function() { return ''; }
    };

    this.fragmentRouter = window.DeskPRO_FragmentRouter;
    this.fragmentRouter.setBaseUrl(window.DP_BASE_URL);

    this.messageChanneler.poller.send();

    // Used by the poller to send flag to update the last active time
    $(document).on('click mousemove keypress', function() {
      self.activityTime = new Date();
    });
  }

  getAppPlatform() {
    console.trace();
    return null;
  }

  addOnloadFunction(fn) {
    console.trace();
  }

  disableHashPath(custom_handler) {
    // noop
  }

  enableHashPath() {
    // noop
  }

  loadHashPath(browserHash) {
    // noop
  }

  //#################################################################
  //# Global registry, getters
  //#################################################################

  getLastClientMessageId() {
    if (this.messageChanneler.lastMessageId) {
      return this.messageChanneler.lastMessageId;
    }

    return 0;
  }

  forwardClientMessageData(data) {
    if (this.messageChanneler.handleMessageAjax) {
      this.messageChanneler.handleMessageAjax(data);
    }
  }

  getPoller() {
    return this.messageChanneler.poller;
  }

  getTabWatcher() {
    return this.tabWatcher;
  }

  getTabStrip() {
    return this.TabBar;
  }

  getDisplayName(type, id) {
    console.trace();
    return '[placeholder]'
  }

  getAgentInfo(agent_id) {
    console.trace();
    return null;
  }

  getTeamInfo(team_id) {
    console.trace();
    return null;
  }

  getUrl(name, vars) {
    console.trace();
    return null;
  }

  getData(name) {
    console.trace();
    return null;
  }


  //#################################################################
  //# Simple UI features
  //#################################################################

  showAlert(msg, classname) {
    console.trace();
  }

  showConfirm(msg, callback_yes, callback_no, phrase_yes, phrase_no, w, h) {
    console.trace();
  }

  showPrompt(msg, callback_ok, callback_cancel) {
    console.trace();
  }

  _initAlertOverlay() {
    console.trace();
  }

  _initConfirmOverlay() {
    console.trace();
  }

  _initPromptOverlay() {
    console.trace();
  }

  showRefreshAlert(admin_name) {
    console.trace();
  }

  //#################################################################
  //# Routes and page loading
  //#################################################################

  addListPage(page) {
    // noop
  }

  setListPage(page, noswitch) {
    // noop
  }

  getListPage() {
    console.trace();
    return null;
  }

  addPageTab(page) {
    DeskPRO_Window.TabBar.addTab(page);
  }

  removePage(page) {
    DeskPRO_Window.TabBar.removeTabById(page.meta.tabId);
  }

  addPageRouteLoader(prefix, callback) {
    if (this.routePrefixes[prefix] == undefined) {
      this.routePrefixes[prefix] = [];
    }

    this.routePrefixes[prefix].push(callback);
  }

  runPageRoute(route, extraData) {
    var found_listener = false;

    var data = this.parseRoute(route);
    if (extraData) {
      data = Object.merge(extraData, data);
    }

    Object.each(this.routePrefixes, function(listeners, prefix) {
      if (route.indexOf(prefix) == 0) {
        Array.each(listeners, function(callback) {
          callback(data);
          found_listener = true;
        });
        if (data.stopListeners) {
          return true;
        }
      }
    }, this);

    if (!found_listener) {
      DP.console.error('Unknown route: %s', route);
    }
  }

  parseRoute(route) {
    // Like:
    // master.masterTag:sectioninfo:moreinfo:url/here/at/end
    // (There might not be any sectioninfo)
    // Example:
    // listpane:/agent/ticket-search/filter/123

    var sections = route.split(':');
    var master = sections.shift();
    var masterTag = null;
    if (master.indexOf('.') != -1) {
      var tmp = master.split('.');
      master = tmp.shift();
      masterTag = tmp.pop();
    }

    var url = sections.pop();

    var data = {
      'route': route,
      'master': master,
      'masterTag': masterTag,
      'sections': sections,
      'url': url,
      stopListeners: false
    };

    return data;
  }

  runPageRouteFromElement(el, extraData) {

    el = $(el);

    if (el.is('.route-do-confirm')) {
      if (!el.is('.did-confirm')) {
        DeskPRO_Window.showConfirm('Are you sure?', function() {
          el.addClass('did-confirm');
          DeskPRO_Window.runPageRouteFromElement(el);
        });
        return;
      }
      el.removeClass('did-confirm');
    }

    if (el.is('.cancel-route')) {
      return;
    }

    if (!el.data('route')) {
      DP.console.warn('Element has no route: %o', el);
      DP.console.trace();
      return;
    }

    extraData = extraData || {};
    extraData.routeTriggerEl = el;
    if (el.data('route-title')) {
      extraData.title = el.data('route-title');
      if (extraData.title == '@text') {
        extraData.title = el.text().trim().replace(/[\n\r]/g, ' ').replace(/\s+/g, ' ');
      } else if (extraData.title == '@title') {
        extraData.title = el.attr('title');
      } else if (extraData.title.test(/^@selector\((.*?)\)$/)) {
        var sel = extraData.title.match(/^@selector\((.*?)\)$/)[1];
        var titleEl = null;
        if (sel[0] == "#") {
          titleEl = $(sel);
        } else {
          titleEl = $(sel, el);
        }

        if (titleEl && titleEl.length) {
          extraData.title = titleEl.text().trim().replace(/[\n\r]/g, ' ').replace(/\s+/g, ' ');
        } else {
          delete extraData.title;
        }
      }
    }
    if (el.data('route-openclass')) {
      extraData.toggleOpenClass = el.data('route-openclass');
    }

    if (el.data('route-notabreload')) {
      extraData.noToggle = true;
      extraData.focus = true;
    } else {
      if (el.closest('#dp_content_wrap')[0] || el.closest('.popover-wrapper')[0]) {
        extraData.noToggle = true;
        extraData.focus = true;
      }
    }

    if (el.data('route-preload-id')) {
      extraData.preloadId = el.data('route-preload-id');
    }

    if (!this.paneVis.tabs) {
      extraData.noToggle = true;
      extraData.focus = true;
    }

    if (el.data('route-replacetab')) {
      extraData.replaceTab = true;
    } else if (el.hasClass('row-item') && !this.paneVis.tabs) {
      extraData.replaceTab = true;
    }

    var isShiftClick = false;
    if (extraData.event && extraData.event.shiftKey) {
      isShiftClick = true;
    }

    if (el.data('route-newtab') || isShiftClick) {
      extraData.replaceTab = false;
      extraData.focus = false;

      if (isShiftClick) {
        extraData.noToggle = true;
      }
    }


    if (!isShiftClick) {
      // this should be handled only when click event occurs
      if (0 === el.data('route').indexOf('listpane:')) {
        this.$scope.showList();
      } else {
        this.$scope.showTabs();
      }
    }

    var popoverEl = el.closest('.popover-wrapper');
    if (popoverEl[0] && popoverEl.data('popover-handler')) {
      popoverEl.data('popover-handler').close(true);
    }

    this.runPageRoute(el.data('route'), extraData);
  }

  loadRoute(routeData) {
    routeData.openInSection = routeData.master;
    switch (routeData.openInSection) {
      case 'listpane':
        break;

      default:
        this.loadPage(routeData.url, routeData);
        break;
    }
  }


  loadRouteOverlay(routeData) {

    var positionAbove = null;
    var zindex = 0;
    var trigger = routeData.routeTriggerEl;
    if (trigger) {
      if (trigger.data('zindex')) {
        zindex = trigger.data('zindex');
      } else {
        var parent = trigger.parentsUntil('body').last();
        if (parent.length && parent.parent().is('body')) {
          positionAbove = parent;
        }
      }
    }

    var fragmentOverlay = new DeskPRO.Agent.PageHelper.FragmentOverlay({
      routeData: routeData,
      positionAbove: positionAbove,
      zIndex: zindex
    });
  }

  loadListPane(url, routeData, callback) {
    console.trace();
  }

  loadPage(url, routeData, callback) {
    var self = this;
    if (!routeData || (!routeData.ignoreExist)) {
      var existTab = DeskPRO_Window.TabBar.findTabByRouteUrl(url);
      if (existTab && (routeData.noToggle || routeData.replaceTab)) {
        if (routeData.focus || routeData.replaceTab) {
          DeskPRO_Window.TabBar.activateTab(existTab);
        }
        return;
      }
      if (existTab && !(existTab.page.allowDupe && existTab.page.TYPENAME != 'loading')) {
        if(routeData && routeData.routeTriggerEl && routeData.routeTriggerEl.data('route-notabreload')) {
          DeskPRO_Window.TabBar.tabToFrontTabById(existTab.id);
          DeskPRO_Window.TabBar.activateTabById(existTab.id);
        } else {
          if (DeskPRO_Window.TabBar.currentTabId == existTab.id) {
            DeskPRO_Window.TabBar.removeTabById(existTab.id);
            if (routeData.routeTriggerEl && routeData.toggleOpenClass) {
              routeData.routeTriggerEl.removeClass(routeData.toggleOpenClass);
            }
          } else {
            DeskPRO_Window.TabBar.activateTab(existTab);
          }
        }
        return;
      }
    }

    var currentActiveTabId = DeskPRO_Window.TabBar.getActiveTabId();

    // Add a temporary tab to the tabstrip
    routeData.tabPlaceholderId = DeskPRO_Window.TabBar.addTabPlaceholder(url, routeData);

    if (currentActiveTabId && routeData && routeData.replaceTab) {
      var currentTab = DeskPRO_Window.TabBar.getTab(currentActiveTabId);
      if (!(currentTab && currentTab.page && currentTab.page.NO_REPLACE_TAB)) {
        DeskPRO_Window.TabBar.removeTabById(currentActiveTabId);
      }
    }

    if (routeData.routeTriggerEl && routeData.toggleOpenClass) {
      routeData.routeTriggerEl.addClass(routeData.toggleOpenClass);
    }

    var successFn = (function(data) {
      try {
        var page = this.createPageFragment(data);
      } catch (e) {
        if (routeData.tabPlaceholderId) {
          DeskPRO_Window.TabBar.removeTabById(routeData.tabPlaceholderId);
        }
        this._showAjaxError('<div class="error-details">There was a problem loading the tab. Here is the raw page output: <textarea class="raw">' + Orb.escapeHtml(data) + '</textarea></div>');
        return;
      }

      page.setMetaData('routeUrl', url);
      if (routeData) {
        page.setMetaData('routeData', routeData);
        if (routeData.tabPlaceholderId) {
          page.setMetaData('tabPlaceholderId', routeData.tabPlaceholderId);
        }
      }
      if (routeData.fragment) {
        page.setMetaData('fragment', routeData.fragment);
      }

      this.addPageTab(page);

      if (routeData.openCallback) {
        routeData.openCallback(page);
      }

      if (callback) callback(page);
    }).bind(this);

    var errorFn = null;

    if (routeData.preloadId) {
      preloadEl = document.getElementById(routeData.preloadId);
      if (preloadEl) {
        var content = preloadEl.innerHTML;
        preloadEl.parentNode.removeChild(preloadEl);
        content = content.replace(/<deskpro_script/g, '<script');
        content = content.replace(/<\/deskpro_script/g, '</script');
        successFn(content);
        return;
      }
    }

    this._doAjaxLoadRoute(url, routeData, successFn, errorFn);
  }



  _doAjaxLoadRoute(url, routeData, successFn, errorFn) {

    routeData = routeData || {};
    if (!url) {
      DP.console.warn('No URL provided! routeData: %o', routeData);
      return;
    }

    var self = this;

    if (routeData && routeData.postData) {
      var ajaxOptions = {
        dataType: 'text',
        url: url,
        type: 'POST',
        data: routeData.postData,
        success: (function(data) {
          successFn(data);
        }).bind(this),
        noErrorOverride: true,
        timeout: 180000
      };

      if (errorFn) {
        ajaxOptions.error = function(jqXHR, textStatus, errorThrown) {
          errorFn(url, routeData, jqXHR, textStatus, errorThrown);
        };
      }

      var xhr = $.ajax(ajaxOptions);

      routeData.xhr = xhr;
    } else {
      var ajaxOptions = {
        dataType: 'text',
        url: url,
        type: 'GET',
        data: routeData.params || null,
        success: (function(data) {
          successFn(data);
        }).bind(this),
        noErrorOverride: true,
        timeout: 180000
      };

      if (routeData.ignore_perm_error) {
        ajaxOptions.ignorePermError = true;
        errorFn = function(url, routeData) {
          if (routeData.tabPlaceholderId) {
            DeskPRO_Window.TabBar.removeTabById(routeData.tabPlaceholderId);
          }
        };
      }

      if (errorFn) {
        ajaxOptions.error = function(jqXHR, textStatus, errorThrown) {
          errorFn(url, routeData, jqXHR, textStatus, errorThrown);
        };
      }

      var xhr = $.ajax(ajaxOptions);

      routeData.xhr = xhr;
    }

    return xhr;
  }



  /**
   * This creates a PageFragment.
   *
   * @param {String} html The HTML page
   * @return {DeskPRO.Agent.PageFragment.Basic}
   */
  createPageFragment(html, classname, force_classname) {

    pageMeta = {
      'title': false,
      'fragmentClass': classname || 'DeskPRO.Agent.PageFragment.Basic'
    };

    var regex = /<script>([\s\S]*?)<\/script>/im;
    var matches = regex.exec(html);

    if (!matches || !matches.length) {
      var regex = /<script\s*type="text\/javascript">([\s\S]*?)<\/script>/im;
      var matches = regex.exec(html);
    }

    if (matches && matches.length) {
      eval(matches[1]);

      // Cut out the pageMeta from the HTML string
      html = html.replace(matches[0], '');
    }

    if (force_classname) {
      pageMeta.fragmentClass = classname;
    }

    // Hard switch that prevents page fragments from
    // rendering a login page into the interface
    // - The login page is redirected to within the code when session expires,
    // so in the template we set this metadata to force this redirect
    if (pageMeta && pageMeta.goToLogin) {
      window.location = BASE_URL + 'old-agent/';

      var page = new DeskPRO.Agent.PageFragment.Basic('');
      return page;
    }

    //DP.console.debug('PageFragment class: %s', pageMeta.fragmentClass);
    var fragment_class = Orb.getNamespacedObject(pageMeta.fragmentClass);

    var page = new fragment_class(html);
    page.setMetaData(pageMeta);

    return page;
  }

  getCurrentListPage() {
    return this.listPage;
  }

  getCurrentTabPage() {
    var tab = DeskPRO_Window.TabBar.getActiveTab();
    if (!tab) return null;

    return tab.page;
  }

  reloadSelectedTab() {
    var tab = DeskPRO_Window.TabBar.getActiveTab();
    if (!tab) return;

    var route = tab.page.meta.routeData.route;

    // Delete current page so its not just deteceted as already loaded
    DeskPRO_Window.TabBar.removeTabById(tab.id);

    this.runPageRoute(route);
  }

  reloadSelectedList() {
    if (!this.listPage) {
      return;
    }

    var route = this.listPage.meta.routeData.route;
    this.runPageRoute(route);
  }

  getMessageChanneler() {
    return this.messageChanneler;
  }

  dismissHelpMessage(el) {
    // noop
    console.trace();
  }

  playSound(files, setOptions) {
    // noop
  }

  playLibrarySound(name, options) {
    // noop
  }

  handleSoundElements(el) {
    // noop
  }

  //#################################################################
  //# AJAX and loading
  //#################################################################

  _globalHandleAjaxComplete(event, xhr, ajaxOptions) {
    // TODO
  }

  showUpdateRunning() {
    // noop
  }

  _globalHandleAjaxError(event, xhr, ajaxOptions, errorThrown, force) {
    // TODO
  }

  incNetworkError() {
    console.trace();
  }

  _showAjaxError(message, type) {
    console.trace();
  }



  //#################################################################
  //# Inits
  //#################################################################

  _initBasic() {

    this.options.messageChanneler.interval = DP_POLLER_INTERVAL;
    this.messageChanneler = new DeskPRO.MessageChanneler.AjaxChanneler(this.messageBroker, this.options.messageChanneler);

    this.messageChanneler.poller.addData((function () {
      return {'at': parseInt(this.activityTime.getTime() / 1000)};
    }).bind(this), 'at', { recurring: true });

    // Add chats we're looking at right now
    this.messageChanneler.poller.addData((function () {
      var chatIdsData = [];
      Array.each(this.getTabWatcher().findTabType('userchat'), function(t) {
        chatIdsData.push({
          name: 'chat_ids[]',
          value: t.page.meta.conversation_id
        });
      });

      if (!chatIdsData.length) {
        return false;
      }

      return chatIdsData;
    }).bind(this), 'chat_ids', { recurring: true });

    this.getTabWatcher().addTabTypeWatcher('userchat', new DeskPRO.Agent.WindowElement.TabWatcher.UserChat());
  }

  _initRoutes() {
    this.addPageRouteLoader('page', this.loadRoute.bind(this));
    this.addPageRouteLoader('article', this.loadRoute.bind(this));
    this.addPageRouteLoader('download', this.loadRoute.bind(this));
    this.addPageRouteLoader('news', this.loadRoute.bind(this));
    this.addPageRouteLoader('feedback', this.loadRoute.bind(this));
    this.addPageRouteLoader('org', this.loadRoute.bind(this));

    var loaded = {};
    this.addPageRouteLoader('ticket', (function(routeData) {

      routeData.forTypename = 'ticket';

      var m = routeData.url.match(/tickets\/([0-9]+)/);
      if (!m || !m[1]) {
        console.warn('Bad page loader call: ' + routeData.url + ' %o', routeData);
        return;
      }
      var ticketId = m[1];

      if (loaded[ticketId]) return;

      routeData.tabLoad = function() {
        loaded[ticketId] = setTimeout(function(){ delete loaded[ticketId] }, 400);
        DeskPRO_Window.getMessageBroker().sendMessage('ui.ticket.opened', { ticketId: ticketId });
      };
      routeData.tabUnload = function() {
        DeskPRO_Window.getMessageBroker().sendMessage('ui.ticket.closed', { ticketId: ticketId });
      };
      this.loadRoute(routeData);
    }).bind(this));
    this.addPageRouteLoader('person', this.loadRoute.bind(this));
    this.addPageRouteLoader('kb_article_view', this.loadRoute.bind(this));
    this.addPageRouteLoader('kb_article_new', this.loadRoute.bind(this));
    this.addPageRouteLoader('kb_article_edit', this.loadRoute.bind(this));
    this.addPageRouteLoader('poppage', this.loadRouteOverlay.bind(this));

    var self = this;
  }

  _initWindowInterface() {
    var self = this;

    this.notifications = new DeskPRO.Agent.Notifications();

    // Global AJAX handler for errors if no error handler is attached
    $(document).ajaxError(this._globalHandleAjaxError.bind(this));
    $(document).ajaxComplete(this._globalHandleAjaxComplete.bind(this));

    // The favicon count
    this.faviconBadge = new DeskPRO.FaviconBadge({
      favicon: '#favicon'
    });

    this.notifications.addEvent('modCount', function(data) {
      var count = data.count || 0;

      var doanim = false;
      if (!$('html').is('.window-active')) {
        doanim = true;
      }

      self.faviconBadge.updateBadge(count, true);
    });

    var autostart = false;

    if (DESKPRO_PERSON_PERMS['agent_tickets.create']) {
      var self = this;
      this.newTicketLoader = new DeskPRO.Agent.Widget.BackgroundPopout({
        loadUrl: BASE_URL + 'old-agent/tickets/new',
        tabRoute: 'page:' + BASE_URL + 'old-agent/tickets/new',
        autostart: autostart
      });
      this.newTicketLoader.newLinkedTicket = function(ticket_id, message_id) {
        self.newTicketLoader.nextParams = {
          ticket_id: ticket_id,
          message_id: message_id || 0
        };
        self.newTicketLoader.open();
      };
      $('#create_ticket_btn').on('click', function() { DeskPRO_Window.newTicketLoader.toggle(); });
    }

    if (DESKPRO_PERSON_PERMS['agent_people.create']) {
      this.newPersonLoader = new DeskPRO.Agent.Widget.BackgroundPopout({
        loadUrl: BASE_URL + 'old-agent/people/new',
        tabRoute: 'page:' + BASE_URL + 'old-agent/people/new',
        autostart: autostart
      });
      $('#create_person_btn').on('click', function() { DeskPRO_Window.newPersonLoader.toggle(); });
    }

    if (DESKPRO_PERSON_PERMS['agent_org.create']) {
      this.newOrganizationLoader = new DeskPRO.Agent.Widget.BackgroundPopout({
        loadUrl: BASE_URL + 'old-agent/organizations/new',
        tabRoute: 'page:' + BASE_URL + 'old-agent/organizations/new',
        autostart: autostart
      });
      $('#create_organization_btn').on('click', function() { DeskPRO_Window.newOrganizationLoader.toggle(); });
    }

    if (DESKPRO_PERSON_PERMS['agent_publish.create']) {
      this.newArticleLoader = new DeskPRO.Agent.Widget.BackgroundPopout({
        loadUrl: BASE_URL + 'old-agent/kb/article/new',
        tabRoute: 'page:' + BASE_URL + 'old-agent/kb/article/new',
        autostart: autostart
      });
      this.newNewsLoader = new DeskPRO.Agent.Widget.BackgroundPopout({
        loadUrl: BASE_URL + 'old-agent/news/new',
        tabRoute: 'page:' + BASE_URL + 'old-agent/news/new',
        autostart: autostart
      });
      this.newDownloadLoader = new DeskPRO.Agent.Widget.BackgroundPopout({
        loadUrl: BASE_URL + 'old-agent/downloads/new',
        tabRoute: 'page:' + BASE_URL + 'old-agent/downloads/new',
        autostart: autostart
      });
      this.newFeedbackLoader = new DeskPRO.Agent.Widget.BackgroundPopout({
        loadUrl: BASE_URL + 'old-agent/feedback/new',
        tabRoute: 'page:' + BASE_URL + 'old-agent/feedback/new',
        autostart: autostart
      });
    }

    DeskPRO_Window.getMessageBroker().addMessageListener('agent.ui.reload', function (info) {
      DeskPRO_Window.showRefreshAlert(info.person_name);
    });

    this.keyboardShortcuts = new DeskPRO.Agent.KeyboardShortcuts();
  }

  initStickyTips(els) {
    if (!els.hasClass('with-stickytip')) {
      els = els.find('.with-stickytip');
    }

    els.each(function() {
      if ($(this).hasClass('dp-stickytip-init')) {
        return;
      }

      var me = $(this);
      $(this).addClass('dp-stickytip-init');

      $(this).one('mouseover', function() {
        $(this).attr('title', '');
        var target = $(me.data('stickytip-target'));
        me.data('stickytip-target', target);

        var hideTimout = null;
        var hideFn = function() {
          if (target.hasClass('over') || me.hasClass('over')) {
            return;
          }

          target.hide();
          target.removeClass('over');
          me.removeClass('over');
        };

        var showFn = function() {
          var pos = me.offset();
          target.css({
            left: pos.left,
            top: pos.top + 15
          });
          target.show();
        };

        var hideTimeout = null;

        target.on('mouseover', function() {
          target.addClass('over');
          if (hideTimeout) {
            window.clearTimeout(hideTimeout);
            hideTimeout = null;
          }
        }).on('mouseout', function() {
          target.removeClass('over');
          if (hideTimeout) {
            window.clearTimeout(hideTimeout);
            hideTimeout = null;
          }
          hideTimeout = window.setTimeout(hideFn, 240);
        });

        me.on('mouseover', function() {
          me.addClass('over');
          showFn();
          if (hideTimeout) {
            window.clearTimeout(hideTimeout);
            hideTimeout = null;
          }
        }).on('mouseout', function() {
          me.removeClass('over');
          if (hideTimeout) {
            window.clearTimeout(hideTimeout);
            hideTimeout = null;
          }
          hideTimeout = window.setTimeout(hideFn, 240);
        });

        me.addClass('over');
        target.detach().appendTo('body');
        showFn();
      });
    });
  }

  _initSections() {
    // noop
    console.trace();
  }

  switchToSection(section_id, no_load_list) {
    // TODO?
  }

  getOpenSection() {
    console.trace();
    return null;
  }

  _initLayout() {

    this.layout = new DeskPRO.Agent.Layout.DeskproWindow();
    this.layout.doResize(true);

    this.TabBar = new DeskPRO.Agent.WindowElement.TabBar({
      tabPane: $('#tabNavigationPane'),
      bodyPane: $('#dp_content_wrap'),
      menuBtn: $('#tabDropdownPicker')
    });

    this.tabWatcher = new DeskPRO.Agent.TabWatcher({
      tabManager: DeskPRO_Window.TabBar
    });

    this.tabWatcher.addTabTypeWatcher('ticket', new DeskPRO.Agent.WindowElement.TabWatcher.Tickets());

    this.recentTabs = new DeskPRO.Agent.RecentTabs();
  }

  _initInterfaceServices() {
    var self = this;

    this.popover_inited = {};

    this.initInterfaceLayerEvents(document);
  }

  _initInterfacePopover(el, opennow) {
    var self = this;
    var popover_inited = this.popover_inited;

    var route = el.data('route');
    var routeData = self.parseRoute(route);

    if (el.data('route-preload-id')) {
      routeData.preloadId = el.data('route-preload-id');
    }

    var popover;

    if (!popover_inited[route]) {

      popover = new DeskPRO.Agent.PageHelper.Popover({
        pageUrl: routeData.url,
        preloadId: routeData.preloadId,
        tabRoute: route,
        loadTimeout: (el.is('.preload') ? 1500 : 0)
      });

      popover_inited[route] = {
        count: 0,
        popover: popover
      };

      popover.addEvent('close', function() {
        if (popover_inited[route].count < 1) {
          popover_inited[route].popover.destroy();
          delete popover_inited[route];
        }
      });

      popover.addEvent('destroy', function() {
        delete popover_inited[route];
      });
    } else {
      popover = popover_inited[route].popover;
    }

    popover_inited[route].count++;

    var tabWrapper = el.closest('.with-page-fragment');
    if (tabWrapper.length) {
      var page = tabWrapper.data('page-fragment');
      page.addEvent('destroy', function() {
        if (popover_inited[route]) {
          popover_inited[route].count--;
          if (popover_inited[route].count < 1) {
            popover_inited[route].popover.destroy();
            delete popover_inited[route];
          }
        }
      });
    } else {
      popover.options.destroyOnClose = true;
    }

    return popover;
  }

  /**
   * Attaches central handlers on a layer. These handlers are added to the document,
   * but if you have a new layer that prevents propagation up to the document,
   * then you'll need to init it as a new layer with its own handlers.
   *
   * @param context
   */
  initInterfaceLayerEvents(context) {
    var self = this;
    if ($(context).is('.dp-interface-layer')) {
      return;
    }

    $(context).addClass('dp-interface-layer');
    var cancelRouteSelection = function(ev) {
      ev.preventDefault();
      ev.stopPropagation();

      // This is because shift-clicking a non-link can result
      // in text selection
      if (ev.shiftKey) {
        if (document.getSelection) {
          document.getSelection().removeAllRanges();
        }
      }
    };

    window.setTimeout(function() {
      // Accept clicks on routes
      $(context).on('mousedown', '[data-route]', function(ev) {
        cancelRouteSelection(ev);
      });
      $(context).on('click', '[data-route]', function(ev) {
        if ($(this).is('.as-popover') || $(this).is('.cancel-route')) {
          return;
        }

        if ($(this).is('.row-item') && (!$(ev.target).is('.click-through') && $(ev.target).is('input, a, button, textarea'))) {
          return;
        }

        cancelRouteSelection(ev);

        self.runPageRouteFromElement($(this), { event: ev });

        // If this was a list-pane and we have an open popover,
        // we need to close the popover so the listpane can actually load
        if ($(this).data('route').indexOf('listpane:') === 0) {
          Object.each(DeskPRO.Agent.PageHelper.Popover_Instances, function(inst) {
            if (inst.isOpen()) {
              inst.close();
            }
          }, this);
        }
      });

      $(context).on('click', '.agent-link', function(ev) {
        ev.preventDefault();
        ev.stopPropagation();

        var agentId = $(this).data('agent-id');
        DP.console.log('Agent click %i', agentId);
        if (!agentId || agentId === '0' || agentId === '' || agentId == DESKPRO_PERSON_ID) {
          return;
        }

        if (!DeskPRO_Window.sections.agent_chat_section) {
          DP.console.warn('The agent chat section is not enabled');
          return;
        }

        DeskPRO_Window.sections.agent_chat_section.newChatWindow([agentId]);
      });

      $(context).on('click', '.as-popover', function(ev) {
        ev.preventDefault();
        ev.stopPropagation();
        self._initInterfacePopover($(this)).toggle();
      });
    }, 100);

    window.setTimeout(function() {
      $(context).find('.tipped').one('mouseover', function(ev) {

        if ($(this).hasClass('tipped-inited')) {
          return;
        }
        $(this).addClass('tipped-inited');

        var options = {};
        if ($(this).data('tipped-options')) {
          eval('options = {' + $(this).data('tipped-options') + '}');
        }

        qtipOptions = {};

        if (options.ajax) {
          qtipOptions.content = {
            text: 'Loading...',
            ajax: {
              url: $(this).data('tipped'),
              type: 'GET'
            }
          };
        } else if ($(this).data('tipped')) {
          qtipOptions.content = {
            attr: 'data-tipped'
          };
        } else {
          qtipOptions.content = {
            attr: 'title'
          };
        }

        if (options.inline) {
          qtipOptions.content.attr = null;
          var el = $('#' + $(this).data('tipped'));
          qtipOptions.content.text = function() {
            return Orb.escapeHtml(el.text());
          };
        }

        if (qtipOptions.content.attr && !$(this).data('as-html')) {
          var me = $(this);
          var attr = qtipOptions.content.attr;
          qtipOptions.content.text = function() {
            return Orb.escapeHtml(me.attr(attr) || '');
          };
          qtipOptions.content.attr = null;
        }

        qtipOptions.style = {
          classes: 'ui-tooltip-shadow ui-tooltip-rounded'
        };

        qtipOptions.position = {
          my: 'top center',
          at: 'bottom center',
          viewport: $(window)
        };

        qtipOptions = $.extend(true, qtipOptions, options);

        $(this).qtip(qtipOptions).qtip('show', ev);
        $(this).addClass('tipped-inited');
      });
    }, 200);

    $('.timeago', context).timeago();
    DeskPRO.ElementHandler_Exec(context);
  }

  initInterfaceServices(context) {
    var self = this;
    var page = false;

    if (context.hasClass('dp-inited-iface')) {
      return;
    }

    if (context.is('.with-page-fragment')) {
      page = context.data('page-fragment');
    } else {
      var tabWrapper = context.closest('.with-page-fragment');
      page = tabWrapper.data('page-fragment');
    }

    $('.as-popover.preload', context).each(function() {
      var p = self._initInterfacePopover($(this));
    });

    $('.timeago', context).timeago();
    DeskPRO.ElementHandler_Exec(context);

    $('input.dp-checkbox', context).each(function() {
      DeskPRO_Window.util.dpCheckbox($(this));
    });

    window.setTimeout(function() {
      $(context).find('.tipped').one('mouseover', function(ev) {

        if ($(this).hasClass('tipped-inited')) {
          return;
        }
        $(this).addClass('tipped-inited');

        var options = {};
        if ($(this).data('tipped-options')) {
          eval('options = {' + $(this).data('tipped-options') + '}');
        }

        qtipOptions = {};

        if (options.ajax) {
          qtipOptions.content = {
            text: 'Loading...',
            ajax: {
              url: $(this).data('tipped'),
              type: 'GET'
            }
          };
        } else if ($(this).data('tipped')) {
          qtipOptions.content = {
            attr: 'data-tipped'
          };
        } else {
          qtipOptions.content = {
            attr: 'title'
          };
        }

        if (options.inline) {
          qtipOptions.content.attr = null;
          var el = $('#' + $(this).data('tipped'));
          qtipOptions.content.text = function() {
            return el.html();
          };
        }

        if (qtipOptions.content.attr && !$(this).data('as-html')) {
          var me = $(this);
          var attr = qtipOptions.content.attr;
          qtipOptions.content.text = function() {
            return Orb.escapeHtml(me.attr(attr) || '');
          };
          qtipOptions.content.attr = null;
        }

        qtipOptions.style = {
          classes: 'ui-tooltip-shadow ui-tooltip-rounded'
        };

        qtipOptions.position = {
          my: 'top center',
          at: 'bottom center',
          viewport: $(window)
        };

        qtipOptions = $.extend(true, qtipOptions, options);

        $(this).qtip(qtipOptions).qtip('show', ev);
      });
    }, 200);
  }

  getSectionData(section_id, callback, extra_data) {
    // noop
    console.trace();
  }

  getSectionDataStartQueue() {
    // noop
    console.trace();
  }

  getSectionDataSendQueued() {
    // noop
    console.trace();
  }

  prepareWidgetedHtml(html) {
    console.trace();
  }

  runWidgetedJs(page, src, inline) {
    console.trace();
  }

  canUseAgentReplyRte() {
    return true;
  }

  initRteAgentReply(textarea, options) {
    return DeskPRO.Agent.RteEditor.initRteAgentReply(textarea, options);
  }

  initAgentNotifierForRte(obj, textarea, agentMap, alwaysAvailable, verifyCallback) {
    var api = textarea.data('redactor');
    if (!api) {
      return;
    }

    if (!agentMap) {
      return;
    }

    var ed = textarea.getEditor();
    var self = this;

    delete agentMap[0];

    var agentMapLower = {}, hasAgents = false;
    Object.each(agentMap, function(data, agentId) {
      hasAgents = true;
      agentMapLower[agentId] = data.name.toLowerCase();
    });

    if (!hasAgents) {
      return;
    }

    obj.agentNotifyList = $('<ul />').addClass('message-agent-notify-list').hide().appendTo(document.body);

    var insertAgentNotify = function(agentId) {
      if (typeof agentMap[agentId] === 'undefined') {
        return;
      }

      self.hideAgentNotifyList(obj);

      if (verifyCallback) {
        if (!verifyCallback(agentId)) {
          return;
        }
      }

      var focus = api.getFocus(),
        focusNode = $(focus[0]),
        testText;

      if (!focus || !focus[0]) {
        return;
      }

      if (focus[0].nodeType == 3) {
        testText = focusNode.text().substring(0, focus[1]);
      } else {
        focus[0] = focusNode.contents().get(focus[1] - 1);
        focusNode = $(focus[0]);
        testText = focusNode.text();
        focus[1] = testText.length;
      }

      var	lastAt = testText.lastIndexOf('@'),
        matches = [];

      if (lastAt != -1) {
        api.setSelection(focus[0], lastAt, focus[0], focus[1]);
      }

      // web kit handles content editable without an issue. this prevents the span
      // from being extended unnecessarily
      var editable = $.browser.webkit ? ' contenteditable="false"' : '';
      api.insertHtml('<span' + editable + ' data-notify-agent-id="' + agentId + '">@' + Orb.escapeHtml(agentMap[agentId].name) + '</span>&nbsp;');
    };

    obj.agentNotifyList.on('mousedown', 'li', function(e) {
      e.preventDefault();
      insertAgentNotify($(this).data('agent-id'));
    });

    ed.on('click blur', function() {
      if (obj.isNote || alwaysAvailable) {
        self.hideAgentNotifyList(obj);
      }
    });

    ed.on('keydown', function(e) {
      if (!obj.isNote && !alwaysAvailable) {
        self.hideAgentNotifyList(obj);
        return;
      }

      switch (e.keyCode) {
        case 38: // up
        case 40: // down
        case 13: // enter
          if (!obj.agentNotifyList.is(':visible')) {
            return;
          }
          break;

        default:
          return;
      }

      e.preventDefault();

      if (e.keyCode == 13) { // enter - inserting the selected
        var li = obj.agentNotifyList.find('li.selected');
        if (!li.length) {
          li = obj.agentNotifyList.find('li:first');
        }

        insertAgentNotify(li.data('agent-id'));
      } else if (e.keyCode == 40) { // down - moves down the list
        var li = obj.agentNotifyList.find('li.selected');
        if (!li.length) {
          obj.agentNotifyList.find('li:first').addClass('selected');
        } else {
          li.removeClass('selected');
          var next = li.next('li');
          if (next.length) {
            next.addClass('selected');
          } else {
            obj.agentNotifyList.find('li:first').addClass('selected');
          }
        }
      } else if (e.keyCode == 38) { // up - moves up the list
        var li = obj.agentNotifyList.find('li.selected');
        if (!li.length) {
          obj.agentNotifyList.find('li:last').addClass('selected');
        } else {
          li.removeClass('selected');
          var prev = li.prev('li');
          if (prev.length) {
            prev.addClass('selected');
          } else {
            obj.agentNotifyList.find('li:last').addClass('selected');
          }
        }
      }
    });

    ed.on('keyup', function(e) {
      if (!obj.isNote && !alwaysAvailable) {
        return;
      }

      if (e.ctrlKey || e.metaKey) {
        return;
      }

      switch (e.keyCode) {
        case 16: // shift
        case 17: // ctrl
        case 18: // alt
        case 19: // pause/break
        case 20: // caps lock
        case 91: // left windows
        case 92: // right windows
        case 93: // select
        case 224: // apple key
          return;

        case 13: // enter
        case 38: // up
        case 40: // down
          // these don't hide as that messes up the keydown handler
          e.stopImmediatePropagation();
          e.preventDefault();
          return;

        case 9: // tab
        case 27: // esc
        case 33: // page up
        case 34: // page down
        case 35: // end
        case 36: // home
        case 37: // left
        case 39: // right
          self.hideAgentNotifyList(obj);
          return;

        default:
          // function keys and other special ones
          if (e.keyCode >= 112 && e.keyCode <= 145) {
            self.hideAgentNotifyList(obj);
            return;
          }
      }

      var focus = api.getFocus(),
        origin = api.getOrigin(),
        selection = api.getSelection();

      if (focus[0] != origin[0] || focus[1] != origin[1]) {
        // selected multiple points, don't show
        self.hideAgentNotifyList(obj);
        return;
      }

      var	focusNode = $(focus[0]),
        testText = focus[0].nodeType == 3 ? focusNode.text().substring(0, focus[1]) : $(focusNode.contents().get(focus[1] - 1)).text(),
        lastAt = testText.lastIndexOf('@'),
        matches = [];

      if (lastAt != -1 && (lastAt == 0 || testText[lastAt - 1].match(/^(\s|[\.!?:;,()<>|/-])$/))) {
        var afterAt = testText.substring(lastAt + 1, testText.length).toLowerCase();

        if (afterAt.length >= 2 && afterAt.length < 75) {
          Object.each(agentMap, function(data, agentId) {
            if (agentMapLower[agentId].indexOf(afterAt) == 0) {
              matches.push(agentId);
            }
          });
        }
      }

      if (matches.length) {
        var selectedId = obj.agentNotifyList.find('li:selected').data('agent-id');

        obj.agentNotifyList.empty();
        for (var i = 0; i < matches.length; i++) {
          var li = $('<li>')
            .text(agentMap[matches[i]].name)
            .css('background-image', 'url('+agentMap[matches[i]].picture_url+')')
            .data('agent-id', matches[i]);
          if (matches[i] === selectedId) {
            li.addClass('selected');
          }
          obj.agentNotifyList.append(li);
        }

        if (!obj.agentNotifyList.find('li:selected').length) {
          obj.agentNotifyList.find('li:first').addClass('selected');
        }

        var containingNode = focus[0].nodeType == 3 ? focusNode.parent() : focusNode;
        if (!containingNode.is('div, p, li, ul, ol, blockquote, table, body')) {
          containingNode = containingNode.closest('div, p, li, ul, ol, blockquote, table, body');
        }
        var offset = containingNode.offset();

        if (selection) {
          var selOffset = Orb.getSelectionCoords(selection);
          if (selOffset) {
            offset = selOffset;
          }
        }

        obj.agentNotifyList.css({
          top: offset.top - obj.agentNotifyList.outerHeight() - 1,
          left: offset.left
        });

        obj.agentNotifyList.show();
        obj.agentNotifyListShown = true;
      } else {
        self.hideAgentNotifyList(obj);
      }
    });

    // this is important as I need this keyup handler to run before redactor's own because of new line handling
    ed.data('events').keyup.reverse();
  }

  hideAgentNotifyList(obj) {
    if (obj.agentNotifyList && obj.agentNotifyListShown) {
      obj.agentNotifyList.empty().hide();
      obj.agentNotifyListShown = false;
    }
  }


  dumpDom() {
    console.trace();
  }

  //##################################################################################################################
  // Notices Window
  //##################################################################################################################

  openNotices() {
    console.trace();
  }

  _updateNoticeEl() {
    console.trace();
  }

  getNoticeEl() {
    console.trace();
  }

  dismissAllNotices() {
    console.trace();
  }

  loadNextNotice() {
    console.trace();
  }

  loadPrevNotice() {
    console.trace();
  }

  setPaneVis(id, vis) {
    console.trace();
  }

  setPaneVisNum(num) {
    console.trace();
  }

  getPaneVisNum() {
    console.trace();
  }

  isSingleColMode() {
    console.trace();
  }
}

export default Agent_DeskPRO_Window;
