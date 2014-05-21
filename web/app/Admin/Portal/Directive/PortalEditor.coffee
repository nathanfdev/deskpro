define [
	'DeskPRO/Util/Util',
	'ColorPicker'
], (
	Util
) ->
	####################################################################################################################
	# Adapter for popups
	####################################################################################################################

	DeskPRO_getPlainTpl = (el) ->
		el = $(el)
		html = el.get(0).innerHTML
		html = html.replace(/%startScript%/g, '<script>')
		html = html.replace(/%endScript%/g, '</script>')
		return html

	modalMarkup = """
		<div class="modal" ng-style="9999">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header" style='display: none'>
						<button type="button" class="close close-trigger">&times;</button>
						<h4 class="modal-title"></h4>
					</div>
					<div class="modal-body"><i class="spinner-flat"></i></div>
					<div class="modal-footer" style='display: none'></div>
				</div>
			</div>
		</div>
	"""
	createOverlayModal = (options) ->
		wrapperEl = $(modalMarkup)
		wrapperEl.hide().appendTo('body')

		inst = {
			elements: {
				wrapper: wrapperEl
			},
			open: ->
				wrapperEl.show()
			close: ->
				wrapperEl.hide()
				if options.destroyOnClose
					wrapperEl.remove()
		}

		applyToBody = (el) ->
			el = $(el)

			headerEl = el.find('.overlay-title')
			with_header = false
			if headerEl[0]
				headerEl.detach()
				title = headerEl.find('h4')
				close = headerEl.find('.close-overlay, .close-trigger')

				if title[0] or close[0]
					with_header = true

				if title[0]
					title.detach()
					wrapperEl.find('.modal-title').empty().append(title.html())
				if not close[0]
					wrapperEl.find('.modal-header').find('.close').hide()

				wrapperEl.find('.modal-header').show()

			if not with_header
				wrapperEl.find('.modal-header').remove()

			footerEl = el.find('.overlay-footer')
			if footerEl[0]
				wrapperEl.find('.modal-footer').empty().append(footerEl)
				wrapperEl.find('.modal-footer').find('.clean-white').removeClass('clean-white').addClass('btn btn-default')
				wrapperEl.find('.modal-footer').show()
			else
				wrapperEl.find('.modal-footer').remove()

			body = el.find('.overlay-content')
			if body[0]
				body = body.contents()
			else
				body = el

			wrapperEl.find('.modal-body').empty().append(body)
			wrapperEl.find('.modal-body').find('input[type="text"], textarea').addClass('form-control')
			wrapperEl.find('.modal-body').find('button').addClass('btn btn-default')

			wrapperEl.find('.close-trigger').on('click', (ev) ->
				ev.preventDefault()
				inst.close()
			)

			if options.fullScreen
				w = $(window).width() - 150
				h = $(window).height() - 250
				wrapperEl.find('.modal-dialog').width(w).find('.modal-body').height(h)
			else
				wrapperEl.find('.modal-dialog').width(768)

		if options.contentElement
			options.contentElement.detach()
			applyToBody(options.contentElement)
			if options.onBeforeOverlayOpened
				options.onBeforeOverlayOpened({ wrapperEl: wrapperEl, overlay: inst })

		else if options.contentAjax
			editorAjaxClient(options.contentAjax).done( (data) ->
				applyToBody(data)
				if options.onContentSet
					options.onContentSet({ wrapperEl: wrapperEl, overlay: inst })
			)

		return inst


	####################################################################################################################
	# Legacy controller
	####################################################################################################################

	editorAjaxClient = (options) ->
		options.headers = {
			'X-DeskPRO-API-Token': window.DP_API_TOKEN,
			'X-DeskPRO-Session-ID': window.DP_SESSION_ID,
			'X-DeskPRO-Request-Token': window.DP_REQUEST_TOKEN
		}
		return $.ajax(options)

	`var PortalEditorController = function() {
		this.init();
	}
	Util.extend(PortalEditorController.prototype, {
		init: function() {
			var self = this;

			// This is so userland can send us messages
			window.PortalEditor = this;

			this.el = $('#admin_portal_editor');
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

			var self = this;

			data = data || {};
			console.log("New message: %s %o", id, data);

			switch (id) {
				case 'loaded':
					this.iframeLoaded(data.height);
					break;
				case 'update_height':
					this.updateHeight(data.height);
					break;
				case 'switch_page':
					console.log("Switched to " + data.path)
					break;
				case 'enable_logo_area':
					editorAjaxClient({
						type: 'POST',
						url: DP_BASE_URL + 'admin/portal-editor/save-editor/enable_logo_area',
						type: 'POST'
					});
					break;
				case 'disable_logo_area':
					editorAjaxClient({
						type: 'POST',
						url: DP_BASE_URL + 'admin/portal-editor/save-editor/disable_logo_area',
						type: 'POST'
					});
					break;
				case 'open_placeholder_editor':
					var controller = data.controller;

					this.showHtmlEditor(controller.name, function(action) {
						switch (action) {
							case 'update': controller.update(); break;
							case 'reset': controller.reset(); break;
						}
					});
					break;
				case 'reset_placeholder':
					var controller = data.controller;
					var template_name;
					if (controller.name == 'header') {
						template_name = 'UserBundle::custom-header.html.twig';
					} else if (controller.name == 'head_include') {
						template_name = 'UserBundle::custom-headinclude.html.twig';
					} else if (controller.name == 'welcome') {
						template_name = 'UserBundle:Portal:welcome-block.html.twig';
					} else if (controller.name == 'articles_header') {
						template_name = 'UserBundle:Articles:section-header.html.twig';
					} else if (controller.name == 'news_header') {
						template_name = 'UserBundle:News:section-header.html.twig';
					} else if (controller.name == 'downloads_header') {
						template_name = 'UserBundle:Downloads:section-header.html.twig';
					} else if (controller.name == 'feedback_header') {
						template_name = 'UserBundle:Feedback:section-header.html.twig';
					} else {
						template_name = 'UserBundle::custom-footer.html.twig';
					}

					editorAjaxClient({
						type: 'DELETE',
						url: DP_BASE_URL + 'api/templates/' + template_name
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

					editorAjaxClient({
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

					editorAjaxClient({
						url: url,
						type: 'POST',
						dataType: 'json',
						data: postData
					});

					break;
				case 'toggle_tab':

					var tabName = data.tabName;
					var on = data.on ? 1 : 0;

					editorAjaxClient({
						url: DP_BASE_URL + 'admin/portal-editor/save-editor/toggle_tab',
						type: 'POST',
						data: {
							tab: tabName,
							on: on
						}
					});
					break;
				case 'reorder_tabs':
					var order = data.order;

					var postData = [];
					for (var i = 0; i < order.length; i++) {
						postData.push({
							name: 'display_order[]',
							value: order[i]
						});
					}

					editorAjaxClient({
						url: DP_BASE_URL + 'admin/portal-editor/save-editor/reorder_tabs',
						type: 'POST',
						dataType: 'json',
						data: postData
					});

					break;
				case 'open_logo_editor':
					var controller = data.controller;
					var overlay = createOverlayModal({
						contentMethod: 'ajax',
						destroyOnClose: true,
						contentAjax: {
							url: DP_BASE_URL + 'admin/portal-editor/get-editor/logo'
						},
						onContentSet: function(ev) {
							var wrapper = ev.wrapperEl;

							wrapper.find('.file-upload').fileupload({
								url: DP_BASE_URL + 'admin/portal-editor/accept-upload',
								dropZone: wrapper,
								autoUpload: true,
								done: function(e, data) {
									file = data.result.pop();
									html =  "<input type=\"hidden\" class=\"new_blob_auth_id\" value=\""+file.blob_auth_id+"\" />";
									html += "<input type=\"hidden\" class=\"new_logo_url\" value=\""+file.download_url+"\" />";
									html += "<img src=\""+file.download_url+"\" class=\"pic-new\" />";

									wrapper.find('.upload-result').html(html);

									wrapper.find('.upload-loading').hide();
									wrapper.find('.save-btn-wrap').show();
									wrapper.find('.upload-result').show();
								},
								progressall: function(e, data) {
									wrapper.find('.upload-loading').show();
								}
							})

							$('.save-logo-trigger', wrapper).on('click', function() {
								var url = $('input.new_logo_url', wrapper).val();
								if (!url) {
									alert('You need to upload an image');
									return;
								}

								controller.setLogo(url);

								editorAjaxClient({
									url: DP_BASE_URL + 'admin/portal-editor/save-editor/header_logo',
									type: 'POST',
									data: {
										blob_authid: wrapper.find('input.new_blob_auth_id').val()
									}
								});

								ev.overlay.close();
							});

							$('.save-text-trigger').on('click', function() {
								controller.setLogoText($('input[name="title"]', wrapper).val(), $('input[name="tagline"]', wrapper).val());

								editorAjaxClient({
									url: DP_BASE_URL + 'admin/portal-editor/save-editor/header_title',
									type: 'POST',
									data: {
										title: wrapper.find('input.title').val(),
										tagline: wrapper.find('input.tagline').val()
									}
								});

								ev.overlay.close();
							});
						}
					});
					overlay.open();
					break;
				case 'open_portal_title':
					var controller = data.controller;
					var overlay = createOverlayModal({
						contentMethod: 'ajax',
						destroyOnClose: true,
						contentAjax: {
							url: DP_BASE_URL + 'admin/portal-editor/get-editor/portal-title'
						},
						onContentSet: function(ev) {
							var wrapper = ev.wrapperEl;
							$('.save-text-trigger').on('click', function() {
								controller.setTitle($('input[name="title"]', wrapper).val());

								editorAjaxClient({
									url: DP_BASE_URL + 'admin/portal-editor/save-editor/portal_title',
									type: 'POST',
									data: {
										title: wrapper.find('input.title').val()
									}
								});

								ev.overlay.close();
							});
						}
					});
					overlay.open();
					break;

				case 'edit_twitter_sidebar_block':
					var controller = data.controller;
					var overlay = createOverlayModal({
						contentMethod: 'ajax',
						destroyOnClose: true,
						contentAjax: {
							url: DP_BASE_URL + 'admin/portal-editor/get-editor/twitter-sidebar'
						},
						onContentSet: function(ev) {
							var wrapper = ev.wrapperEl;
							$('.save-trigger').on('click', function() {
								editorAjaxClient({
									url: DP_BASE_URL + 'admin/portal-editor/save-editor/twitter_sidebar',
									type: 'POST',
									data: {
										twitter_name: wrapper.find('input.twitter_name').val(),
										max_items: wrapper.find('input.max_items').val()
									}
								});

								ev.overlay.close();

								controller.update()
							});
						}
					});
					overlay.open();
					break;

				case 'new_sidebar_block':
					this.showHtmlEditor('NEW_SIDEBAR_BLOCK', function(action, data) {
						switch (action) {
							case 'update': self.tellPortal('new_sidebar_block', {
								pid: data.pid
							});
						}
					});
					break;

				case 'edit_template_block':
					var controller = data.controller;
					this.showHtmlEditor('EDIT_SIDEBAR_BLOCK:' + data.pid, function(action, data) {
						switch (action) {
							case 'update': controller.update();
							break;
						}
					});
					break;

				case 'new_sidebar_block_simple':
					this.showHtmlEditorSimple(0, function(action, data) {
						switch (action) {
							case 'update': self.tellPortal('new_sidebar_block_simple', {
								pid: data.pid
							});
						}
					});
					break;

				case 'edit_sidebar_block_simple':
					var controller = data.controller;
					this.showHtmlEditorSimple(data.pid, function(action, data) {
						switch (action) {
							case 'update': controller.update();
							break;
						}
					});
					break;

				case 'delete_sidebar_block_simple':
					var controller = data.controller;
					var el = controller.getEl();
					el.hide();

					editorAjaxClient({
						type: 'POST',
						url: DP_BASE_URL + 'admin/portal-editor/sideblock-simple/'+data.pid+'/delete.json',
						error: function() {
							el.show();
						},
						success: function() {
							controller.remove();
						}
					});
					break;

				case 'delete_template_block':
					var controller = data.controller;
					var el = controller.getEl();
					el.hide();

					editorAjaxClient({
						type: 'POST',
						url: DP_BASE_URL + 'admin/portal-editor/blocks/' + data.pid + '/delete-template-block.json',
						error: function() {
							el.show();
						},
						success: function() {
							controller.remove();
						}
					});
					break;
			}
		},


		/**
		 * Shows a generic HTML editor
		 *
		 * @param callback
		 */
		showHtmlEditor: function(name, callback) {
			var el = $(DeskPRO_getPlainTpl($('#admin_portal_block_html_edit_tpl')));

			var template_name;
			if (name == 'header') {
				template_name = 'UserBundle::custom-header.html.twig';
			} else if (name == 'head_include') {
				template_name = 'UserBundle::custom-headinclude.html.twig';
			} else if (name == 'welcome') {
				template_name = 'UserBundle:Portal:welcome-block.html.twig';
			} else if (name == 'articles_header') {
				template_name = 'UserBundle:Articles:section-header.html.twig';
			} else if (name == 'downloads_header') {
				template_name = 'UserBundle:Downloads:section-header.html.twig';
			} else if (name == 'feedback_header') {
				template_name = 'UserBundle:Feedback:section-header.html.twig';
			} else if (name == 'NEW_SIDEBAR_BLOCK') {
				// TemplatesController::saveTemplateAction knows to treat this special
				template_name = 'UserBundle:Portal:new-sidebar-block.html.twig';
			} else if (name.indexOf('EDIT_SIDEBAR_BLOCK:') !== -1) {
				// TemplatesController knows to treat this special
				template_name = name;
			} else {
				template_name = 'UserBundle::custom-footer.html.twig';
			}

			var overlay = createOverlayModal({
				contentElement: el,
				destroyOnClose: true,
				fullScreen: true,
				onBeforeOverlayOpened: function(evData) {
					var el = evData.wrapperEl

					if (el.is('.has-init')) return;
					el.addClass('has-init');

					el.find('textarea').val('').addClass('loading');

					editorAjaxClient({
						url: DP_BASE_URL + 'api/templates/' + template_name,
						context: this,
						success: function(data) {
							var val = data.template_code.code
							el.find('textarea').val(val).removeClass('loading');
						}
					});

					$('.save-trigger', el).on('click', function() {

						el.find('.overlay-footer').addClass('loading');

						var code = el.find('textarea').val().trim();
						var postData = {
							template: {
								code: code
							}
						};

						if (name == 'head_include') {
							if (!code.length) {
								editorAjaxClient({
									type: 'DELETE',
									url: DP_BASE_URL + 'api/templates/' + template_name,
									success: function() {
										window.location.reload(false);
									}
								});
							} else {
								editorAjaxClient({
									url: DP_BASE_URL + 'api/templates/' + template_name,
									context: this,
									type: 'POST',
									data: postData,
									success: function(data) {
										window.location.reload(false);
									}
								});
							}
							return;
						}

						if (!code.length) {
							editorAjaxClient({
								type: 'DELETE',
								url: DP_BASE_URL + 'api/templates/' + template_name
							});

							callback('reset');
							overlay.close();
						} else {
							editorAjaxClient({
								url: DP_BASE_URL + 'api/templates/' + template_name,
								context: this,
								type: 'POST',
								data: postData,
								success: function(data) {
									el.find('.overlay-footer').removeClass('loading');

									if (data.error) {
										alert(data.error_message + "\n\nLine: " + data.error_line);
										return;
									}

									callback('update', data);
									overlay.close();
								}
							});
						}
					});
				}
			});
			overlay.open();
		},

		/**
		 * Shows a simple editor for title/content
		 *
		 * @param callback
		 */
		showHtmlEditorSimple: function(pid, callback) {
			pid = parseInt(pid) || 0;

			var el = $(DeskPRO_getPlainTpl($('#admin_portal_block_simple_html_edit_tpl')));

			var overlay = createOverlayModal({
				contentElement: el,
				destroyOnClose: true,
				fullScreen: true,
				onBeforeOverlayOpened: function(evData) {
					var el = evData.overlay.elements.wrapper;

					if (el.is('.has-init')) return;
					el.addClass('has-init');

					if (pid) {
						el.find('textarea.content').val('').addClass('loading');

						editorAjaxClient({
							url: DP_BASE_URL + 'admin/portal-editor/sideblock-simple/' + pid + '.json',
							context: this,
							dataType: 'json',
							success: function(data) {
								el.find('input.title').val(data.title);
								el.find('textarea.content').val(data.content).removeClass('loading');
							}
						});
					}

					el.find('textarea.content').height($(window).height() - 320);

					$('.save-text-trigger', el).on('click', function() {

						el.find('.overlay-footer').addClass('loading');

						var postData = [];
						postData.push({
							name: 'title',
							value: el.find('input.title').val()
						});
						postData.push({
							name: 'content',
							value: el.find('textarea.content').val()
						});

						editorAjaxClient({
							url: DP_BASE_URL + 'admin/portal-editor/sideblock-simple/'+pid+'/save.json',
							context: this,
							type: 'POST',
							data: postData,
							success: function(data) {
								el.find('.overlay-footer').removeClass('loading');
								callback('update', data);
								overlay.close();
							}
						});
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
				console.error("Could not get iframe document");
			}

			this.iframeWindow = this.iframeDocument.window;

			$('#portal_iframe').height(height + 25);
			$('#dp_fauxbrowser').height(height + 188);
			this.iframeQuery('html').css('overflow', 'hidden');
			this.iframeQuery('body').css('overflow', 'hidden');

			// Show the frame now
			$('#portal_iframe_loading').fadeOut(300, function() {
				$('#portal_iframe').css('opacity', '1');
			});
		},

		updateHeight: function(height) {
			$('#portal_iframe').height(height + 25);
			$('#dp_fauxbrowser').height(height + 188);
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
			var backdrop = $('<div style="z-index: 99999; position: absolute; top:0; right: 0; bottom: 0; left: 0;" />').hide().appendTo('body');

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

						swatchEl.closest('.style-row').find('.color-reset').addClass('enabled');
					}
				});
			});

			var colorResets = $('.color-reset', panel);
			colorResets.on('click', function(ev) {
				ev.preventDefault();
				ev.stopPropagation();

				var swatchEl = $(this).closest('.style-row').find('.color-swatch');
				var setColor = $(this).data('color').replace('#', '');

				console.log('Setting %s on %e', setColor, swatchEl);
				swatchEl.ColorPickerSetColor(setColor);
				swatchEl.data('color', '#' + setColor);
				$('div', swatchEl).css('backgroundColor', '#' + setColor);

				$(this).removeClass('enabled');
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

				editorAjaxClient({
					url: DP_BASE_URL + 'admin/portal-editor/save-editor/css_var',
					type: 'POST',
					data: formData,
					success: function() {
						self.tellPortal('reload_css');
					}
				});
			});
		}
	});`

	####################################################################################################################

	PortalEditor = [ ->
		return {
			restrict: 'A',
			transclude: true,
			template: "<div class=\"dp-portal-editor-frame\" ng-transclude></div>",
			scope: {},
			link: (scope, element, attrs) ->
				ctrl = new PortalEditorController();
				return
		}
	]

	return PortalEditor