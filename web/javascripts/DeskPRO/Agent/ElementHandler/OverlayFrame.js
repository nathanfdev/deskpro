Orb.createNamespace('DeskPRO.Agent.ElementHandler');

DeskPRO.Agent.ElementHandler.OverlayFrame = new Orb.Class({
	Extends: DeskPRO.ElementHandler,

	initPage: function() {
		var self = this;
		this.frameUrl  = this.el.data('frame-url') || this.el.attr('href');
		this.frameId   = this.el.data('frame-id');
		this.closeKey  = this.el.data('close-key');

		this.frameWrap = null;
		this.frame = null;

		this.el.on('click', function(ev) {
			Orb.cancelEvent(ev);
			self.open();
		});
	},

	initFrame: function(with_hash) {
		this.frameWrap = $('<div class="overlay-frame-wrap" style="position: absolute; top: 0; right: 0; bottom: 0; left: 0; z-index: 9999999999;"></div>');
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

		this.frameWrap.appendTo('body');
	},

	open: function(with_hash) {
		var self = this;
		if (!this.frame) {
			this.initFrame(with_hash);
		}

		var url = this.frame.attr('src');
		if (url.indexOf('#') !== -1) {
			hash = url.substr(url.indexOf('#')+1);
		} else {
			hash = '';
		}

		this.setHash(hash);
		this.frameWrap.show();

		DeskPRO_Window.disableHashPath(function(hash) {
			if (hash.indexOf('admin:') !== 0) {
				return;
			}

			var frame = self.frame.get(0);
			var iWin = frame.contentWindow;
			var localHash = hash.substr(6);

			if (localHash == '/back_to_agent') {
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

	setHash: function (hash) {
		if (!hash) {
			hash = '';
		}

		hash = this.frameId + ':' + hash;
		window.location.hash = '#' + hash;
	},

	close: function() {
		if (this.frameWrap) {
			this.frameWrap.remove();
			this.frame = null;
			this.frameWrap = null;
			delete window['DP_FRAME_OVERLAY_' + this.frameId];
		}

		window.location.hash = '';
		DeskPRO_Window.enableHashPath();
	}
});
