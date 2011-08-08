Orb.createNamespace('DeskPRO.Agent.Widget');

DeskPRO.Agent.Widget.SnippetViewer = new Orb.Class({

	Implements: [Orb.Util.Options, Orb.Util.Events],

	initialize: function(options) {
		this.options = {
			viewUrl: null,
			triggerElement: null
		};

		this.setOptions(options);

		if (this.options.triggerElement) {
			var self = this;
			$(this.options.triggerElement).click(function(ev) {
				ev.preventDefault();
				ev.stopPropagation();

				self.open();
			});
		}
	},

	_init: function() {
		if (this._hasInit) return;
		if (this._isInitting) return;

		this._isInitting = true;

		$.ajax({
			url: this.options.viewUrl,
			type: 'GET',
			dataType: 'html',
			context: this,
			success: function(html) {
				this._init2(html);
			}
		});
	},

	_init2: function(html) {
		var self = this;

		this.wrapper = $(html);

		// Set up the tabs
		this.catTabs = new DeskPRO.UI.SimpleTabs({
			triggerElements: $('nav:first > ul > li', this.wrapper),
			context: this.wrapper
		});

		this.overlay = new DeskPRO.UI.Overlay({
			contentElement: this.wrapper,
			destroyOnClose: false
		});

		this.wrapper.delegate('.snippet-trigger', 'click', function(ev) {

			ev.preventDefault();
			ev.stopPropagation();

			var snippetId = $(this).data('snippet-id');
			var snippetEl = $('.snippet-' + snippetId);
			var snippet = $('textarea.value.formatted, textarea.value.raw').first().text().trim();

			var evData = {
				event: ev,
				snippetId: snippetId,
				snippetEl: snippetEl,
				snippet: snippet
			};

			self.fireEvent('snippetClick', [evData]);

			self.overlay.close();
		});

		this._isInitting = false;
		this._hasInit = true;

		if (this._showNow) {
			this.open();
		}
	},

	open: function() {
		if (!this._hasInit) {
			this._showNow = true;
			this._init();
			return;
		}

		var evData = {
			snippetViewer: this,
			overlay: this.overlay,
			cancel: false
		};
		this.fireEvent('beforeOpen', [evData]);

		if (evData.cancel) {
			return;
		}

		this.overlay.open();

		delete evData.cancel;
		this.fireEvent('open', [evData]);
	},

	close: function() {
		var evData = {
			snippetViewer: this,
			overlay: this.overlay,
			cancel: false
		};
		this.fireEvent('beforeClose', [evData]);

		if (evData.cancel) {
			return;
		}

		this.overlay.close();

		delete evData.cancel;
		this.fireEvent('open', [evData]);
	},

	destroy: function() {

		var evData = {
			snippetViewer: this,
			overlay: this.overlay
		};
		this.fireEvent('beforeDestroy', [evData]);

		this.overlay.destroy();
		this.wrapper.remove();

		this.fireEvent('destroy', [evData]);
	}
});