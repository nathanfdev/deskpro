Orb.createNamespace('DeskPRO.Admin.ElementHandler');

DeskPRO.Admin.ElementHandler.PortalEditor = new Orb.Class({
	Extends: DeskPRO.ElementHandler,

	init: function() {
		var self = this;

		// This is so userland can send us messages
		window.PortalEditor = this;

		$('#portal_iframe').attr('src', this.el.data('portal-url'));

		$(':checkbox.section-toggle').change(function() {
			var type = $(this).attr('name');

			if ($(this).is(':checked')) {
				self.tellPortal('app_enabled', {name: type});
			} else {
				self.tellPortal('app_disabled', {name: type});
			}
		});
	},

	tellPortal: function(id, data) {
		this.iframeWindow.PortalAdmin.acceptMessage(id, data);
	},

	callPortal: function(id, data) {
		return this.iframeWindow.PortalAdmin[id](data);
	},

	acceptMessage: function(id, data) {

		data = data || {};
		console.log("New message: %s %o", id, data);

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
			case 'open_logo_editor':
				var controller = data.controller;
				var overlay = new DeskPRO.UI.Overlay({
					contentMethod: 'ajax',
					destroyOnClose: true,
					contentAjax: {
						url: BASE_URL + 'admin/portal/get-editor/logo'
					},
					onContentSet: function(ev) {
						var wrapper = ev.overlay.getElement();

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

						$('.save-logo-trigger', wrapper).click(function() {
							var blobId = $('input.new_blob_id', wrapper).val();
							if (!blobId) {
								alert('You need to upload a file');
								return;
							}

							var url = $('input.new_logo_url', wrapper).val();

							controller.setLogo(url);
							ev.overlay.close();
						});

						$('.save-text-trigger').click(function() {
							controller.setLogoText($('input[name="title"]').val(), $('input[name="tagline"]').val());
							ev.overlay.close();
						});
					}
				});
				overlay.open();
				break;
		}
	},

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

				$('.save-trigger', el).click(function() {
					callback(cm.getValue());
					overlay.close();
				});
			}
		});
		overlay.open();
	},

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
			console.error("Could not get iframe document");
		}

		this.iframeWindow = this.iframeDocument.window;

		$('#portal_iframe').height(height + 25);
		this.iframeQuery('html').css('overflow', 'hidden');
		this.iframeQuery('body').css('overflow', 'hidden');
	},

	iframeQuery: function(query) {
		return this.iframeWindow.jQuery(query);
	}
});
