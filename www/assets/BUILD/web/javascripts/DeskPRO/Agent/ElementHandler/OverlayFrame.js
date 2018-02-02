Orb.createNamespace('DeskPRO.Agent.ElementHandler');

DeskPRO.Agent.ElementHandler.OverlayFrame = new Orb.Class({
  Extends: DeskPRO.ElementHandler,

  initPage: function() {
    var self = this;
    this.frameUrl  = this.el.data('frame-url') || this.el.attr('href');
    this.frameId   = this.el.data('frame-id');
    this.closeKey  = this.el.data('close-key') || 'back_to_agent';
    this.frameTitle = this.el.data('win-title') || false;

    this.frameWrap = null;
    this.frame = null;
    this.callback = null;
    this.opened = false;
    this.isLoaded = false;

    this.el.on('click', function(ev) {
      Orb.cancelEvent(ev);
      self.open();
    });

    self.preload();

    if (this.el.data('frame-preload')) {
      this.frame.attr('src', this.url);
      this.isLoaded = true;
    }
  },

  initFrame: function() {
    this.frameWrap = $('<div/>', {
      id:    'overlay-frame-' + this.frameId,
      css:   {
        display:         'none',
        position:        'absolute',
        top:             '51px',
        right:           0,
        bottom:          0,
        left:            '52px',
        zIndex:          1799,
        backgroundColor: '#F5F7FA'
      }
    });
    this.frameWrap.addClass('overlay-frame-wrap');

    this.frame = $('<iframe/>', {
      frameborder:  0,
      width:        '100%',
      height:       '100%',
      scrolling:    'auto',
      marginheight: 0,
      marginwidth:  0,
      css:          {
        margin:  0,
        padding: 0
      }
    });

    this.frame.appendTo(this.frameWrap);

    window.addEventListener('message', this.receiveMessage, false);
    window['DP_FRAME_OVERLAY_' + this.frameId] = this;

    if (!window.DP_FRAME_OVERLAYS) {
      window.DP_FRAME_OVERLAYS = {};
    }

    window.DP_FRAME_OVERLAYS[this.frameId] = this;

    this.frameWrap.appendTo('body');
  },

  preload: function(withHash, callback) {
    if (!this.frame) {
      this.initFrame(withHash);
    }

    this.callback = callback;
    this.url = this.frameUrl;

    if (withHash) {
      if (this.url.indexOf('#') !== -1) {
        this.url = this.url.substr(0, this.url.indexOf('#'));
      }

      this.url += '#' + withHash;
    }
  },

  open: function(path, callback) {

    if (!this.isLoaded) {
      this.frame.attr('src', this.url);
      this.isLoaded = true;
    }

    this.opened = true;
    this.frameWrap.css('display', 'block');

    window.DeskPRO_Window.keyboardShortcuts.isPaused = true;

    var event = new CustomEvent('dpOpenOverlayFrame', { 'detail': { id: this.frameId, path: path } });
    window.document.dispatchEvent(event);

    if (path) {
      var frameWindow = this.getFrameWindow();
      frameWindow.location.href = this.frameUrl + '#' + path;
    }

    if (!path && this.url.indexOf('#') !== -1) {
      path = this.url.substr(this.url.indexOf('#') + 1);
    }

    window.document.addEventListener('dpIframeLoaded', function loadHash() {
      frameWindow.location.hash = path;
      window.document.removeEventListener('dpIframeLoaded', loadHash);
    });

    this.setHash(path);
    this.frameWrap.show();

    if (this.frameTitle) {
      this.originalTitle = document.title;
      document.title = this.frameTitle;
    }

    if (callback) {
      callback();
    }
  },

  callLoaded: function() {
    if (this.callback) {
      this.callback();
    }

    var loadingEl = document.getElementById('dp_loading');
    if (loadingEl && loadingEl.parentNode) {
      // loadingEl.parentNode.removeChild(loadingEl);
    }
  },

  setHash: function (path) {
    if (path === '#/go_to_agent') {
      return;
    }

    path = path || '/';
    path = path.replace(/^#/, '');

    if (navigator.appName === 'Microsoft Internet Explorer' ||
      (navigator.appName === 'Netscape' && navigator.appVersion.indexOf('Edge') !== -1) ||
      (navigator.appName === 'Netscape' && navigator.appVersion.indexOf('Trident') !== -1)) {
      return;
    }
    this.getFrameWindow().location.hash = '#' + path;
    window.location.hash = '#' + this.frameId + ':' + (path || '');

    setTimeout(function() {
      window.document.dispatchEvent(new CustomEvent('dpChangeSection'));
    }, 0);
  },

  close: function() {
    this.opened = false;
    this.frameWrap.css('display', 'none');

    window.DeskPRO_Window.keyboardShortcuts.isPaused = false;

    if (this.frameTitle) {
      document.title = this.originalTitle;
    }

    DeskPRO_Window.enableHashPath();
    DeskPRO_Window.updateWindowUrlFragment();202020202
    var event = new CustomEvent('dpCloseOverlayFrame', { 'detail': { id: this.frameId } });
    window.document.dispatchEvent(event);
  },

  getFrameWindow: function() {
    var frame = this.frame ? this.frame[0] : null;

    return frame ? frame.contentWindow : null;
  },

  deleteFrame: function() {
    if (this.frameWrap) {
      this.frameWrap.remove();
      this.frame = null;
      this.frameWrap = null;
      delete window['DP_FRAME_OVERLAY_' + this.frameId];
      delete window['DP_FRAME_OVERLAYS'][this.frameId];
    }

    if (this.frameTitle) {
      document.title = this.originalTitle;
    }

    window.location.hash = '';
    DeskPRO_Window.enableHashPath();
  },

  receiveMessage: function(event) {
    if (event.isTrusted && event.data) {
      if (event.data.reload) {
        window.location.href = event.data.location;
      }
    }
  }
});


DeskPRO.Agent.ElementHandler.OverlayFrameSimple = new Orb.Class({
  Extends: DeskPRO.Agent.ElementHandler.OverlayFrame,

  initFrame: function(with_hash) {
    this.frameWrap = $('<div class="overlay-frame-wrap" style="position: absolute; top: 51px; right: 0; bottom: 0; left: 55px; z-index: 9999999999;"></div>');
    this.frame = $('<iframe frameborder="0" width="100%" height="100%" scrolling="auto" marginheight="0" marginwidth="0" style="position: absolute; top: 0; right: 0; bottom: 0; left: 0; margin: 0; padding: 0;"></iframe>');
    this.frame.appendTo(this.frameWrap);

    var url = this.frameUrl;
    if (with_hash) {
      if (url.indexOf('#') !== -1) {
        url = url.substr(0, url.indexOf('#'));
      }

      url += '#' + with_hash;
    }
    this.frame.attr('src', url);

    window['DP_FRAME_OVERLAY_' + this.frameId] = this;
    if (!window['DP_FRAME_OVERLAYS']) window['DP_FRAME_OVERLAYS'] = {};
    window['DP_FRAME_OVERLAYS'][this.frameId] = this;

    this.frameWrap.insertAfter('#dp_center');
  },

  open: function(with_hash, callback) {
    this.parent(with_hash, callback);
    $('#dp_center').hide();
    $('#dp_nav_sections').find('.is-nav-section').hide();
    $('#dp_nav_sections').find('.is-nav-btn').removeClass('section-on');
    this.el.closest('li').addClass('section-on');
  },

  close: function() {
    this.parent();
    $('#dp_center').show();
    $('#dp_nav_sections').find('.is-nav-section').hide();
    $('#dp_nav_sections').find('.is-nav-section').filter('.is-default').show();
    $('#dp_nav_sections').find('.is-nav-btn').removeClass('section-on').filter('.is-default').addClass('section-on');
  }
});
