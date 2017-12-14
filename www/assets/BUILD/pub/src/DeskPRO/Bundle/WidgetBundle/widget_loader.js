((window, document) => {
  // #include deskpro_loader_util.js

  const options = window.DESKPRO_WIDGET_OPTIONS;

  // Polyfill for adding CustomEvent
  // https://developer.mozilla.org/fr/docs/Web/API/CustomEvent
  // http://stackoverflow.com/questions/25579986/uncaugth-reference-error-custom-event-not-defind-in-the-file-ratchet
  function WidgetEvent(type, params = { bubbles: false, cancelable: false, detail: undefined }) {
    const event = document.createEvent('CustomEvent');
    event.initCustomEvent(type, params.bubbles, params.cancelable, params.detail);

    return event;
  }

  WidgetEvent.prototype = window.Event.prototype;
  window.WidgetEvent = WidgetEvent;

  const getEventName = type => `dpwidget${type}`;

  // DpWidget api
  window.addEventListener('message', (event) => {
    window.dispatchEvent(new WidgetEvent(getEventName(event.data.type), { detail: event.data.options }));
  }, false);

  const dispatchCustomEvent = (type, eventOptions = {}) => {
    if (window.dp_loader) {
      window.dp_loader.postMessage({ type, options: eventOptions }, '*');
    }
  };

  const addWidgetListener = (type, callback) => window.addEventListener(getEventName(type), callback, false);
  const removeWidgetListener = (type, callback) => window.removeEventListener(getEventName(type), callback, false);
  const getWidgetStatus = () => dispatchCustomEvent('getWidgetStatus');
  const getOnlineAgents = () => dispatchCustomEvent('getOnlineAgents');
  const openWidget = (event) => {
    if (event) {
      event.preventDefault();
    }

    dispatchCustomEvent('openWidget');
  };

  window.DpWidget = {
    addWidgetListener,
    removeWidgetListener,
    getWidgetStatus,
    getOnlineAgents,
    openWidget,
    dispatchCustomEvent
  };

  const each = (classNames, fn) => {
    for (const className of (classNames || '').split(',')) {
      // document.getElementsByClassName broken in Safari
      // https://github.com/zloirock/core-js/issues/37
      const elements = [].slice.call(document.getElementsByClassName(className));
      for (const element of elements) {
        fn(element);
      }
    }
  };

  let hasFiredReadyEvent = false;

  addWidgetListener('widgetStatus', (event) => {
    const response = event.detail;
    const updateStyle = (className, attr, value) => {
      each(className, (el) => {
        el.style[attr] = value;
      });
    };

    if (response.loaded) {
      updateStyle('dpwidget-show-on-loaded', 'display', 'block');
      updateStyle('dpwidget-hide-on-loaded', 'display', 'none');
    }
    if (response.chatAvailable) {
      updateStyle('dpwidget-show-on-available', 'display', 'block');
      updateStyle('dpwidget-hide-on-available', 'display', 'none');
    } else {
      updateStyle('dpwidget-show-on-unavailable', 'display', 'block');
      updateStyle('dpwidget-hide-on-unavailable', 'display', 'none');
    }

    each('dpwidget-open,dp-chat-trigger', (el) => {
      el.onclick = openWidget;
    });

    if (response.loaded && !hasFiredReadyEvent && window.CustomEvent) {
      hasFiredReadyEvent = true;
      const ev = new window.CustomEvent('dpwidget.ready', {
        detail: {
          DpWidget:      window.DpWidget,
          options,
          chatAvailable: !!response.chatAvailable
        }
      });

      window.dispatchEvent(ev);
    }
  });

  // Widget app loader
  getInstInfo(options.helpdeskUrl, options.instId || 'default').then((instInfo) => {     // eslint-disable-line no-undef
    const helpdeskUrl = instInfo.helpdeskUrl;
    const appSrc = `${instInfo.assetUrl}/pub/build/DeskPRO_WidgetBundle.js`;

    const loadFn = () => {
      // Create the iframe loader
      const node = document.createElement('iframe');
      node.src = 'javascript:false';                                                // eslint-disable-line no-script-url
      node.title = '';
      node.role = 'presentation';
      node.name = 'dp_loader';
      (node.frameElement || node).style.cssText = 'visibility: hidden; position: absolute; left: -999; width: 0; height: 0';

      // Insert it into the DOM
      document.body.appendChild(node);

      // This try/catch process is requried for proper crossdomain functioning
      // http://calendar.perfplanet.com/2012/the-non-blocking-script-loader-pattern/#crossdomain_issues
      const frameWin = node.contentWindow;
      const frameDoc = frameWin.document;

      let doc;
      let docDomain;

      try {
        doc = frameDoc;
      } catch (c) {
        docDomain = document.domain;
        node.setAttribute('src ', `javascript:var d=document.open();d.domain="${docDomain}";void(0);`);
        doc = frameDoc;
      }

      // After onload, we load the script source for real and setting constants
      doc.open().customLoad = () => {
        frameWin.DP_HELPDESK_URL = `${helpdeskUrl.replace(/\/$/, '')}/`;
        frameWin.DP_OPTIONS = options;
        // Portal page widget config
        frameWin.DESKPRO_BASE_URL = `${helpdeskUrl.replace(/\/$/, '')}/portal/api/`;
        // Asset URLs
        frameWin.DESKPRO_APP_ASSETS_URL = instInfo.assetUrl;

        frameWin.DP_SEND_VISITOR_TRACK = getPageHitProperties();                         // eslint-disable-line no-undef

        const linkNode = document.createElement('link');
        linkNode.setAttribute('type', 'text/css');
        linkNode.setAttribute('rel', 'stylesheet');
        linkNode.setAttribute('href', `${frameWin.DESKPRO_APP_ASSETS_URL}/pub/build/DeskPRO_WidgetBundle_style.css`);

        doc.body.appendChild(linkNode);

        const appNode = doc.createElement('script');
        appNode.setAttribute('charset', 'UTF8');
        appNode.setAttribute('type', 'application/javascript');
        appNode.setAttribute('src', appSrc);

        if (docDomain) {
          doc.domain = docDomain;
        }

        doc.body.appendChild(appNode);
      };

      doc.write('<body onload="document.customLoad();"><div id="dp_loader_element"></div>');
      doc.close();
    };

    onReadyState(loadFn);                                                                // eslint-disable-line no-undef
  });
})(window, document);
