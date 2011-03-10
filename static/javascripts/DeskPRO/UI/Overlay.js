Orb.createNamespace('DeskPRO.UI');

/**
 * This creates an overlay (optionally modal) whose contents can be fetched via AJAX,
 * or already exist within the page.
 */
DeskPRO.UI.Overlay = new Orb.Class({
	Implements: [Orb.Util.Options, Orb.Util.Events],

	initialize: function(options) {
		
		// Init vars
		this.objectId = null,

		this.options = {
			triggerElement: null,
			contentMethod: 'element',
			contentElement: null,
			contentAjax: {
				url: '',
				type	: 'GET',
				dataType: 'html'
			},
			iframeUrl: null,
			maxHeight: 700,
			maxWidth: 900,
			destroyOnClose: false,
			customClassname: '',
			isModal: true,
			zIndex: 1000000,
			escapeClose: true,
			modalClickClose: true,
			objectGroup: 'default'
		};

		this.isDestroyed = false;
		this.hasInit = false;
		this.hasSentAjax = false;
		this.elements = {};

		if (options) this.setOptions(options);

		if (this.options.triggerElement) {
			this.setupTriggerElement($(this.options.triggerElement));
		}

		if (this.options.escapeClose) {
			$(document).keydown((function (ev) {
				if (ev.which == 27) {
					this.closeOverlay();
				}
			}).bind(this));
		}
	},



	/**
	 * Check to see if the overlay is currently open.
	 */
	isOverlayOpen: function() {
		if (!this.hasInit) return false;
		return this.elements.wrapper.is(':visible');
	},



	/**
	 * Display the overlay
	 */
	openOverlay: function() {
		if (!this.initOverlay()) {
			return;
		}

		if (this.isOverlayOpen()) {
			return;
		}

		this.fireEvent('beforeOverlayOpened', { overlay: this });

		if (!this.options.zIndex) {
			this.options.zIndex = Orb.findHighestZindex()+1;
		}

		this.elements.modal.css({
			'z-index': this.options.zIndex,
			'position': 'absolute',
			'top': 0,
			'right': 0,
			'bottom': 0,
			'left': 0
		});

		this.elements.modal.fadeIn(200);

		if (this.options.contentMethod == 'iframe') {

			var w = $(window).width() - 250;
			var h = $(window).height() - 150;

			if (w > this.options.maxWidth) w = this.options.maxWidth;
			if (h > this.options.maxHeight) h = this.options.maxHeight;

			$('iframe:first', this.elements.wrapper).css({ width: w, height: h });

			var x = ($(window).width() - this.elements.wrapperOuter.outerWidth()) / 2;
			var y = ($(window).height() - this.elements.wrapperOuter.outerHeight()) / 2;

			this.elements.wrapperOuter.css({'left': x, 'top': y});

		} else {
			var w = this.elements.wrapperOuter.outerWidth();
			var pageW = $(document).width();
			var leftForCenter = (pageW / 2) - (w / 2);

			this.elements.wrapperOuter.css({
				'top': 60,
				'left': leftForCenter
			});
		}

		this.elements.wrapperOuter.css({
			'z-index': this.options.zIndex+1,
			'position': 'absolute',
			'left': leftForCenter
		});
		this.elements.wrapperOuter.fadeIn(450, (function() {
			this.fireEvent('overlayOpened', { overlay: this });
		}).bind(this));
	},



	/**
	 * Close/hide the overlay
	 */
	closeOverlay: function() {

		if (!this.isOverlayOpen()) {
			return;
		}

		var eventData = { overlay: this, cancelClose: false };
		this.fireEvent('beforeOverlayClosed', eventData);

		if (eventData.cancelClose) return;

		this.elements.modal.fadeOut(450);
		this.elements.wrapperOuter.fadeOut(200);

		this.fireEvent('overlayClosed', { overlay: this });

		if (this.options.destroyOnClose) {
			this.destroy();
		}
	},



	/**
	 * Initiate the overlay by created the various elements needed etc.
	 */
	initOverlay: function() {

		if (this.hasInit) return true;

		if (this.options.isModal) {
			this.elements.modal = $('<div class="deskpro-overlay-overlay '+this.options.customClassname+'" style="display:none" />');
			this.elements.modal.appendTo('body');

			if (this.options.modalClickClose) {
				this.elements.modal.click((function() {
					this.closeOverlay();
				}).bind(this));
			}
		}

		this.elements.wrapperOuter = $('<div class="deskpro-overlay-outer '+this.options.customClassname+'" style="display:none" />');
		this.elements.wrapperOuter.appendTo('body');

		this.elements.wrapper = $('<div class="deskpro-overlay '+this.options.customClassname+'">');
		this.elements.wrapper.appendTo(this.elements.wrapperOuter);

		switch (this.options.contentMethod) {
			case 'element':

				var el = $(this.options.contentElement);
				this._setContent(el);

				this.hasInit = true;

				return true;
				break;

			case 'ajax':

				// Already sending
				if (this.hasSentAjax) return false;
				this.hasSentAjax = true;

				var ajaxConfig = Object.merge(this.options.contentAjax, {
					success: this._handleAjaxSuccess.bind(this)
				});

				$.ajax(ajaxConfig);

				this.fireEvent('ajaxStart', {
					overlay: this
				});

				return false;
				break;

			case 'iframe':

				var name = 'iframe_' + Orb.uuid();
				var el = $('<iframe name="'+name+'" src="'+this.options.iframeUrl+'"></iframe>');

				this._setContent(el);
				this.hasInit = true;

				this.elements.wrapper.addClass('no-pad').addClass('iframe');

				return true;
				break;
		}

		console.error('Unknown content method: %s', this.options.contentMethod);

		return false;
	},



	/**
	 * Callback used with the ajax content setter when the result was
	 * fetched from the server.
	 */
	_handleAjaxSuccess: function(data) {

		var eventData = {
			overlay: this,
			ajaxData: data
		};
		this.fireEvent('ajaxDone', eventData);

		var el = $('<div>' + eventData.ajaxData + '</div>');
		this._setContent(el);

		this.hasInit = true;

		this.openOverlay();
	},



	/**
	 * Handle setting the content of the overlay.
	 */
	_setContent: function (el) {
		el.detach().appendTo(this.elements.wrapper);

		// Often pages will hide content by default in the initial page,
		// but our wrapper element is hidden so we dont want the innards to be hidden.
		el.show();

		$('.overlay-close-trigger, .close-trigger', el).click((function () {
			this.closeOverlay();
		}).bind(this));

		this.fireEvent('contentSet', {
			overlay: this,
			contentEl: el,
			wrapperEl: this.elements.wrapper
		});
	},



	/**
	 * Set up a click trigger on an element (or elements).
	 *
	 * @param mixed el A selector, an element, or a jQuery collection
	 */
	setupTriggerElement: function(el) {
		el = $(el);

		el.click((function (ev) {
			this.openOverlay();
			ev.preventDefault();
		}).bind(this));
	},


	/**
	 * Destroy this overlay and all of its supporting elements.
	 */
	destroy: function() {
		if (this.elements.wrapperOuter) {
			this.elements.wrapperOuter.remove();
		}
		if (this.elements.modal) {
			this.elements.modal.remove();
		}
		this.isDestroyed = true;

		this.fireEvent('destroyed');
	},


	isDestroyed: function() {
		return this.isDestroyed;
	}
});