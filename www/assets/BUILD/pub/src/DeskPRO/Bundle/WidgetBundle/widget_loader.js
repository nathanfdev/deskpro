((window, document) => {
  // #include deskpro_loader_utils.js

  var getScriptURL = (function() {
    var script = document.getElementById('dp-widget-loader');
    return function() { return script.dataset.helpdeskUrl; };
  })();

  let options = {};

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

  // DpWidget api
  window.addEventListener('message', event => {
    window.dispatchEvent(new WidgetEvent(getEventName(event.data.type), { detail: event.data.options }));
  }, false);

  const getEventName = type => `dpwidget${type}`;
  const dispatchCustomEvent = (type, eventOptions = {}) => {
    if (window.dp_loader) {
      window.dp_loader.postMessage({ type, options: eventOptions }, '*');
    }
  };

  const addWidgetListener = (type, callback) => window.addEventListener(getEventName(type), callback, false);
  const removeWidgetListener = (type, callback) => window.removeEventListener(getEventName(type), callback, false);
  const getWidgetStatus = () => dispatchCustomEvent('getWidgetStatus');
  const getOnlineAgents = () => dispatchCustomEvent('getOnlineAgents');
  const openWidget = event => {
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

  const each = (className, fn) => {
    const elements = document.getElementsByClassName(className);
    for (let i = 0; i < elements.length; i++) {
      fn(elements[i]);
    }
  };

  addWidgetListener('widgetStatus', event => {
    const response = event.detail;
    const updateStyle = (className, attr, value) => {
      each(className, el => {
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

    each('dpwidget-open', el => {
      el.onclick = openWidget;
    });
  });

  window.DESKPRO_WIDGET_INIT = (options) => {
    // Widget app loader
    getInstInfo(options.helpdeskUrl, window, document, options.instId || 'default').then(instInfo => {
      const helpdeskUrl = instInfo.helpdeskUrl;
      const appSrc = instInfo.assetUrl + '/pub/build/DeskPRO_WidgetBundle.js';

      const loadFn = () => {
        // Create the iframe loader
        const node = document.createElement('iframe');
        node.src = 'javascript:false';
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

        frameWin.DP_HELPDESK_URL = helpdeskUrl.replace(/\/$/, '') + '/';
        frameWin.DP_OPTIONS = options;

        // Portal page widget config
        frameWin.DESKPRO_BASE_URL = helpdeskUrl.replace(/\/$/, '') + '/portal/api/';

        // Asset URLs
        frameWin.DESKPRO_APP_ASSETS_URL = instInfo.assetUrl;

        let doc;
        let docDomain;

        try {
          doc = frameDoc;
        } catch (c) {
          docDomain = document.domain;
          node.src = 'javascript:var d=document.open();d.domain="' + docDomain + '";void(0);';
          doc = frameDoc;
        }

        // After onload, we load the script source for real
        doc.open()._load = () => {
          const linkNode = document.createElement('link');
          linkNode.type = 'text/css';
          linkNode.rel = 'stylesheet';
          linkNode.href = frameWin.DESKPRO_APP_ASSETS_URL + '/pub/build/DeskPRO_WidgetBundle_style.css';

          doc.body.appendChild(linkNode);

          const appNode = doc.createElement('script');
          appNode.charset = 'UTF8';
          appNode.type = 'application/javascript';
          appNode.src = appSrc;

          if (docDomain) {
            doc.domain = docDomain;
          }

          doc.body.appendChild(appNode);
        };

        doc.write('<body onload="document._load();"><div id="dp_loader_element"></div>');
        doc.close();
      };

      onReadyState(loadFn, window, document);
    });
  };

  if (typeof window.DESKPRO_WIDGET_OPTIONS !== 'undefined' && window.DESKPRO_WIDGET_OPTIONS.noFetchSettings) {
    options = window.DESKPRO_WIDGET_OPTIONS;
    window.DESKPRO_WIDGET_INIT(options);
  }
  if (Object.keys(options).length === 0 || !window.DESKPRO_WIDGET_OPTIONS.noFetchSettings) {
    let json;
    window.DESKPRO_INIT_TRIGGERED = false;
    if (json = localStorage.getItem('dpWidgetOptions')) {
      options = JSON.parse(json);
      if (!Date.now) {
        Date.now = function() { return new Date().getTime(); }
      }
      if (options && options.date > (Math.floor(Date.now() / 1000) - 15*60)) {
        window.DESKPRO_INIT_TRIGGERED = true;
        window.DESKPRO_WIDGET_INIT(options);
      } else {
        localStorage.removeItem('dpWidgetOptions')
      }
    }
    const helpdeskUrl = getScriptURL() + 'portal/api/widget/widget_options';

    const appNode = document.createElement('script');
    appNode.charset = 'UTF8';
    appNode.type = 'application/javascript';
    appNode.src = helpdeskUrl;

    document.body.appendChild(appNode);
  }
})(window, document);
