Orb.createNamespace('DeskPRO.Admin.ElementHandler');

/**
 * The portal editor is made of up the admin page, and then a specially
 * loaded page in the user interface loaded through an iframe.
 *
 * We call the admin page the PortalEditor, and the user page the PortalClient.
 *
 * Messages, like click events that need an editor, are handled byt he PortalClient
 * and are pssed up to this PortalEditor which takes care of opening editors and saving
 * data. Then in some cases, data is passed back down to the PortalClient to update
 * the live display.
 *
 * Generally: PortalEditor handles saving/changing of data, PortalClient handles displaying data
 * and interaction with the UI.
 */
DeskPRO.Admin.ElementHandler.PortalEditor = new Orb.Class({
	Extends: DeskPRO.ElementHandler,

	init: function() {
		var self = this;

		// This is so userland can send us messages
		window.PortalEditor = this;

		$('#portal_iframe').attr('src', this.el.data('portal-url'));

		$(':checkbox.section-toggle').on('change', function() {
			var type = $(this).attr('name');

			if ($(this).is(':checked')) {
				self.tellPortal('app_enabled', {name: type});
			} else {
				self.tellPortal('app_disabled', {name: type});
			}
		});

		this._initColorPicker();
		$('#portal_iframe').css('opacity', '0');
	},

	/**
	 * Send a message to the portal client
	 *
	 * @param id
	 * @param data
	 */
	tellPortal: function(id, data) {
		this.iframeWindow.PortalAdmin.acceptMessage(id, data);
	},


	/**
	 * Call a method on the portal client
	 *
	 * @param id
	 * @param data
	 */
	callPortal: function(id, data) {
		return this.iframeWindow.PortalAdmin[id](data);
	},


	/**
	 * Accepts a message passed from the portal client
	 *
	 * @param id
	 * @param data
	 */
	acceptMessage: function(id, data) {

		data = data || {};
		DP.console.log("New message: %s %o", id, data);

		switch (id) {
			case 'loaded':
				this.iframeLoaded(data.height);
				break;
			case 'open_placeholder_editor':
				var controller = data.controller;

				this.showHtmlEditor(function(html) {
					controller.setContent(html);
				});
				break;
			case 'update_orders':
				var ids = data.orderedIds;

				var postData = [];
				for (var i = 0; i < ids.length; i++) {
					postData.push({
						name: 'display_order[]',
						value: ids[i]
					});
				}

				$.ajax({
					url: this.el.data('url-update-orders'),
					type: 'POST',
					dataType: 'json',
					data: postData
				});

				break;
			case 'block_toggled':

				var url = this.el.data('url-block-toggle').replace(/_PID_/g, data.pid);
				var postData = {
					enabled: data.enabled ? 1 : 0
				};

				$.ajax({
					url: url,
					type: 'POST',
					dataType: 'json',
					data: postData
				});

				break;
			case 'open_logo_editor':
				var controller = data.controller;
				var overlay = new DeskPRO.UI.Overlay({
					contentMethod: 'ajax',
					destroyOnClose: true,
					contentAjax: {
						url: BASE_URL + 'admin/portal/get-editor/logo'
					},
					onContentSet: function(ev) {
						var wrapper = ev.wrapperEl;

						wrapper.fileupload({
							url: BASE_URL + 'admin/misc/accept-upload',
							dropZone: wrapper,
							autoUpload: true,
							uploadTemplate: $('.template-upload', wrapper),
							downloadTemplate: $('.template-download', wrapper)
						}).bind('fileuploadstart', function() {
							$('p.explain', wrapper).hide();
						}).bind('fileuploadadd', function() {
							$('.files', wrapper).empty();
						});

						$('.save-logo-trigger', wrapper).on('click', function() {
							var blobId = $('input.new_blob_id', wrapper).val();
							if (!blobId) {
								alert('You need to upload a file');
								return;
							}

							var url = $('input.new_logo_url', wrapper).val();

							controller.setLogo(url);
							ev.overlay.close();
						});

						$('.save-text-trigger').on('click', function() {
							controller.setLogoText($('input[name="title"]', wrapper).val(), $('input[name="tagline"]', wrapper).val());
							ev.overlay.close();
						});
					}
				});
				overlay.open();
				break;
		}
	},


	/**
	 * Shows a generic HTML editor
	 *
	 * @param callback
	 */
	showHtmlEditor: function(callback) {
		var el = $(DeskPRO_Window.util.getPlainTpl($('#admin_portal_block_html_edit_tpl')));

		var overlay = new DeskPRO.UI.Overlay({
			contentElement: el,
			destroyOnClose: true,
			onBeforeOverlayOpened: function() {
				if (el.is('.has-init')) return;
				el.addClass('has-init');

				var cm = CodeMirror.fromTextArea($('textarea', el).get(0), {
					mode: "text/html"
				});

				$('.save-trigger', el).on('click', function() {
					callback(cm.getValue());
					overlay.close();
				});
			}
		});
		overlay.open();
	},


	/**
	 * Whent the portal client is loaded, it sends a message to us and we invoke
	 * this method to set up the messages channel.
	 *
	 * @param height
	 */
	iframeLoaded: function(height) {
		var iframe = $('#portal_iframe').get(0);

		if (iframe.contentDocument) {
			this.iframeDocument = iframe.contentDocument;
		} else if (iframe.contentWindow) {
			this.iframeDocument = iframe.contentWindow.document;
		} else if (iframe.document) {
			this.iframeDocument = iframe.document;
		} else {
			this.iframeDocument = null;
			DP.console.error("Could not get iframe document");
		}

		this.iframeWindow = this.iframeDocument.window;

		$('#portal_iframe').height(height + 25);
		this.iframeQuery('html').css('overflow', 'hidden');
		this.iframeQuery('body').css('overflow', 'hidden');

		// Show the frame now
		$('#portal_iframe_loading').fadeOut(300, function() {
			$('#portal_iframe').css('opacity', '1');
		});
	},


	/**
	 * Execute a jQuery query from in the context of the portal client
	 *
	 * @param query
	 */
	iframeQuery: function(query) {
		return this.iframeWindow.jQuery(query);
	},


	_initColorPicker: function() {
		var self     = this;
		var panel    = $('#portal_colors');
		var trigger  = $('#portal_colors_trigger');
		var backdrop = $('<div class="backdrop" style="z-index: 999" />').hide().appendTo('body');

		panel.detach().appendTo('body');

		trigger.on('click', function() {
			if (panel.is(':visible')) {
				closeColorPanel();
			} else {
				openColorPanel();
			}
		});

		backdrop.on('click', function() {
			closeColorPanel();
		});

		var openColorPanel = function() {
			var triggerPos = trigger.offset();

			var top  = triggerPos.top  + trigger.height();
			var left = (triggerPos.left + trigger.width() - 20);

			panel.css({
				top: top ,
				left: left
			});

			panel.slideDown();
			backdrop.show();
		};

		var closeColorPanel = function() {
			panel.slideUp();
			backdrop.hide();
		};

		var colorSwatches = $('.color-swatch', panel);
		colorSwatches.each(function() {
			var swatchEl = $(this);
			swatchEl.on('click', function() {
				swatchEl.ColorPickerShow();
			});

			swatchEl.ColorPicker({
				onSubmit: function(hsb, hex, rgb, el) {
					swatchEl.data('color', hex);
					$(el).ColorPickerHide();
				},
				onBeforeShow: function () {
					$(this).ColorPickerSetColor(swatchEl.data('color'));
				},
				onChange: function (hsb, hex, rgb) {
					$('div', swatchEl).css('backgroundColor', '#' + hex);
					swatchEl.data('color', '#' + hex);
				}
			});
		});

		$('button.apply-trigger', panel).on('click', function() {
			closeColorPanel();

			var formData = [];

			colorSwatches.each(function() {
				formData.push({
					name: 'vars[' + $(this).data('color-id') + ']',
					value: $(this).data('color')
				});
			});

			$.ajax({
				url: BASE_URL + 'admin/portal/save-editor/css_var',
				type: 'POST',
				data: formData,
				success: function() {
					self.tellPortal('reload_css');
				}
			});
		});
	}
});
