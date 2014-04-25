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

		this.el.on('click', function(ev) {
			Orb.cancelEvent(ev);
			self.open();
		});
	},

	initFrame: function() {
		this.frameWrap = $('<div class="overlay-frame-wrap" style="position: absolute; top: 0; right: 0; bottom: 0; left: 0; z-index: 9999999999;"></div>');
		this.frame = $('<iframe frameborder="0" width="100%" height="100%" scrolling="auto" marginheight="0" marginwidth="0" style="position: absolute; top: 0; right: 0; bottom: 0; left: 0; margin: 0; padding: 0;"></iframe>');
		this.frame.appendTo(this.frameWrap);

		window['DP_FRAME_OVERLAY_' + this.frameId] = this;

		if (!window['DP_FRAME_OVERLAYS']) window['DP_FRAME_OVERLAYS'] = {};
		window['DP_FRAME_OVERLAYS'][this.frameId] = this;

		this.frameWrap.appendTo('body');
	},

	open: function(with_hash, callback) {
		var self = this, hash, frameId = this.frameId;

		if (window['DP_FRAME_OVERLAYS']) {
			for (var k in window['DP_FRAME_OVERLAYS']) {
				if (k !== this.frameId && window['DP_FRAME_OVERLAYS'].hasOwnProperty(k)) {
					window['DP_FRAME_OVERLAYS'][k].close();
				}
			}
		}

		if (!this.frame) {
			this.initFrame(with_hash);
		}

		this.callback = callback;

		var url = this.frameUrl;
		if (with_hash) {
			if (url.indexOf('#') !== -1) {
				url = url.substr(0, url.indexOf('#'));
			}

			url += '#' + with_hash
		}

		if (url.indexOf('#') !== -1) {
			hash = url.substr(url.indexOf('#')+1);
		} else {
			hash = '';
		}

		this.setHash(hash);
		this.frameWrap.show();
		this.frame.attr('src', url);

		if (this.frameTitle) {
			this.originalTitle = document.title;
			document.title = this.frameTitle;
		}

		DeskPRO_Window.disableHashPath(function(hash) {
			if (hash.indexOf(self.frameId + ':') !== 0) {
				return;
			}

			var frame = self.frame.get(0);
			var iWin = frame.contentWindow;
			var localHash = hash.substr(frameId.length+1);

			if (localHash == '/' + self.closeKey) {
				self.close();
				return;
			}

			if (!iWin) {
				return;
			}

			try {
				iWin.location.hash = '#' + localHash;
			} catch (e) {}
		});
	},

	callLoaded: function() {
		if (this.callback) {
			this.callback();
		}
	},

	setHash: function (hash) {
		if (!hash) {
			hash = '';
		}

		hash = hash.replace(/^#/, '')
		hash = this.frameId + ':' + hash;
		window.location.hash = '#' + hash;
	},

	close: function() {
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

			url += '#' + with_hash
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