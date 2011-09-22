Orb.createNamespace('DeskPRO.User.ElementHandler');

DeskPRO.User.SuggestedContentOverlay = new Orb.Class({
	Implements: [Orb.Util.Options, Orb.Util.Events],

	initialize: function(options) {
		this.options = {
			template: null,

			url: null,

			contentType: null,
			contentId: null,

			destroyOnClose: true
		};

		this.setOptions(options);

		this.overlayEl = null;
		this.backdropEl = null;
		this.runningAjax = false;
	},

	_initOverlay: function() {
		if (this._hasInit) return;
		this._hasInit = true;

		var self = this;

		this.overlayEl = $(this.options.template).hide().appendTo('body');
		this.controlsWrap = $('.dp-controls', this.overlayEl).hide();
		this.backdropEl = $('<div class="dp-backdrop" />').appendTo('body');
		this.backdropEl.click((function(el) {
			this.close();
		}).bind(this));

		this.runningAjax = $.ajax({
			url: this.options.url,
			type: 'GET',
			context: this,
			error: function() {
				this.close();
			},
			success: function(html) {
				this.controlsWrap.show();
				$('.dp-content-holder', this.overlayEl).empty().html(html);
			}
		});

		$('.dp-section-toggle', this.controlsWrap).click(function(ev) {
			ev.preventDefault();
			var toggleSel = $(this).data('toggle-section');
			$('.dp-control-section', self.controlsWrap).fadeOut('fast', function() {
				window.setTimeout(function() {
					$(toggleSel, self.controlsWrap).fadeIn();
				}, 150);
			});
		});

		this.fireEvent('init', [this.overlayEl, this.controlsEl, this]);
	},

	open: function() {
		this._initOverlay();

		var pos = {
			top: 40,
			left: 100,
			width: null
		};

		this.fireEvent('preOpen', [pos, this]);
		if (pos.cancel) {
			return;
		}

		this.overlayEl.css({
			top: pos.top,
			left: pos.left
		});

		if (pos.width) {
			this.overlayEl.css('width', pos.width);
		}

		this.overlayEl.fadeIn('fast').addClass('open');
		this.backdropEl.show();
	},

	close: function() {
		if (!this._hasInit) return;
		if (!this.overlayEl.is('.open')) {
			return;
		}

		this.overlayEl.fadeOut('fast', (function() {
			if (this.options.destroyOnClose) {
				this.destroy();
			}
		}).bind(this));
		this.backdropEl.hide();
	},

	destroy: function() {
		if (this._hasInit) return;
		this.overlayEl.remove();
		this.backdropEl.remove();

		this.overlayEl = null;
		this.backdropEl = null;

		if (this.runningAjax) {
			this.runningAjax.abort();
			this.runningAjax = null;
		}
	}
});
