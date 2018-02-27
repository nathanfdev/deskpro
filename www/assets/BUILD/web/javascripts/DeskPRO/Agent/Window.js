Orb.createNamespace('DeskPRO.Agent');

/**
 * The super duper Window that connects controls from all over the interface.
 *
 * Contains a shared registry (perhaps not used?), global data like display names for agents
 * and other elements, and data for things like department-to-cat maps.
 *
 * Is also responsible for "routing" and loading page fragments. The router uses strings and decides where
 * they should be loaded (and how). For example, "navpane:filters/", the first part says it'll
 * be a navpane fragment. The second part is a simple URL we can load via AJAX.
 */
DeskPRO.Agent.Window = new Orb.Class({

	Extends: DeskPRO.BasicWindow,

	init: function() {

		this.hashHandling = true;
		this.onloadStack = [];
		this.dismissAlertQueue = [];
		this.routePrefixes = {};

		this.messageChanneler = null;
		this.poller = null;

		this.sections = {};
		this.openSection = null;

		this.listPage = null;

		this.innerLayout = null;

		this._alertOverlay = null;
		this._confirmOverlay = null;

		this.loadingIndicatorEl = null;
		this.loadingIndicatorCount = 0;
		this.ajaxErrorOverlay = null;

		this.cancelHashLoad = 0;
		this.activeListNav = null;
		this.activityTime = new Date();
		this.isMobile = false;

		this.updateWindowUrlFragment = _.debounce(this.updateWindowUrlFragmentNow.bind(this), 100);

		this.appsSidebar = {
			visible: false,
			width: 240
		};

		if (Modernizr.localstorage) {
			if (localStorage['apps_sidebar_state'] && localStorage['apps_sidebar_state'] == 'open') {
				this.appsSidebar.visible = true;
			}
      // disable reading from local storage in favor of the new 240 px width soon to be read from a config file
			// if (localStorage['apps_sidebar_width']) {
			// 	this.appsSidebar.width = localStorage['apps_sidebar_width'];
			// }
		}

		this.agentNotifyListShown = false;

		this.paneVis = {
			source: true,
			list: true,
			tabs: true
		};

		this.paneVisBit = {
			source: 1,
			list: 2,
			tabs: 4
		};

		if (window.AppPlatform) {
			this.initAppPlatform(window.AppPlatform);
		}

		this.util = {
			modCountEl: function(el, op, num) {

				el = $(el);

				if (!num && num !== 0) num = 1;

				var count = parseInt(el.text().trim());

				if (op == '-' || op == 'rem' || op == 'del' || op == 'sub') {
					count -= num;
					if (count < 0) count = 0;
				} else if (op == '+' || op == 'add') {
					count += num;
 				} else {
					count = num;
				}

				el.text(el.text().trim().replace(/(\d+)/, count));

				if (el.data('tag')) {
					$('i.' + el.data('tag')).text(count);
				}

				return count;
			},

			/**
			 * Get a "plain" article. ie of type="text/x-deskpro-plain"
			 *
			 * @param el
			 * @return {String}
			 */
			getPlainTpl: function(el) {

				if (!el) {
					DP.console.error('Invalid template element passed %o', el);
					return '';
				}

				var el = $(el);


				if (!el.length) {
					DP.console.error('No template element passed %o', el);
					return '';
				}

				var html = el.get(0).innerHTML;

				html = html.replace(/%startScript%/g, '<script>');
				html = html.replace(/%endScript%/g, '</script>');

				html = html.replace(/%scriptWord%/g, 'script');

				var uid = Orb.uuid();
				html = html.replace(/%baseId%/g, uid);

				var baseId = Orb.uuid();
				html = html.replace(/%baseId%/g, baseId);

				return html;
			},

			showSavePuff: function(overEl) {

				if (!overEl || !overEl[0]) {
					return;
				}

				var pos = overEl.offset();

				if (!pos) {
					return;
				}

				var el = $('<div class="load-puff" style="display: none; opacity: 0" />');
				el.appendTo('body');

				el.css({
					top: pos.top + 15,
					left: pos.left + overEl.width() - 4
				});

				var endPos1 = pos.top - 5;
				var endPos2 = pos.top - 15;

				el.show();
				el.animate({
					top: endPos1,
					opacity: 1
				}, 200, 'swing', function() {
					window.setTimeout(function() {
						el.animate({
							top: endPos2,
							opacity: 0
						}, 200, 'swing', function() {
							el.remove();
						});
					}, 225);
				});
			},

			ajaxWithClientMessages: function(options) {
				options.dataType = 'json';
				options.withActionAlerts = true;
				return $.ajax(options);
			},

			slugify: function(str) {
				str = str.replace(/[^a-zA-Z0-9\-]/g, '-');
				str = str.replace(/\-{2,}/g, '-');
				str = str.replace(/^\-/, '');
				str = str.replace(/\-$/, '');

				return str;
			},

			linkUrls: function(string) {
				string = string||'';
				string = Orb.linkUrls(string);
				string = string.replace(/<a /g, '<a target="_blank" ');
				return string;
			},

			dpCheckbox: function(input) {
				if (!input.attr('id')) {
					input.attr('id', Orb.getUniqueId('dp_chk'));
				}

				var id = input.attr('id');

				input.hide().addClass('with-dp-checkbox');

				var check = $('<span class="dp-checkbox" data-bound="#'+id+'" />');

				check.attr('id', Orb.getUniqueId('dp_chk'));
				if (input.is(':checked')) {
					check.addClass('checked');
				}

				input.data('bound', '#' + check.attr('id'));

				check.insertAfter(input)

				input.on('change', function(ev) {
					if ($(this).is(':checked')) {
						check.addClass('checked');
					} else {
						check.removeClass('checked');
					}
				});
				check.on('click', function(ev) {
					ev.stopPropagation();
					if (input.is(':checked')) {
						$(this).addClass('checked');
					} else {
						$(this).removeClass('checked');
					}
				});
			},

			fileupload: function(el, options) {

				var $el = $(el),
					plainEl = $el[0],
					blobs = {};

				var setel;
				if (!options) options = {};
        options.pasteZone = null;
				if (options.page) {
					options.namespace = options.page.OBJ_ID + '_fileupload';
					options.page = null;
				}

				if (!options.namespace) {
					options.namespace = Orb.uuid();
				}

				if (!options.dropZone) {
					options.dropZone = $(el);
				}

				if (typeof options.autoUpload == 'undefined') {
					options.autoUpload = true;
				}

				if (!options.url) {
					if (options.saveMedia) {
						options.url = BASE_URL + 'agent/misc/accept-upload?save_media=1';
					} else {
						options.url = BASE_URL + 'agent/misc/accept-upload';
					}
				}

				if (options.uploadTemplate) {
					setel = options.uploadTemplate;
				} else {
					setel = $('.template-upload', el);
				}

				if (!setel || !setel[0]) {
					console.error("Invalid uploadTemplate");
					return $(el);
				}

				if (!setel.attr('id')) {
					var id = Orb.getUniqueId('up');
					setel.attr('id', id);
				} else {
					var id = setel.attr('id');
				}
				delete(options.uploadTemplate);
				options.uploadTemplateId = id;

				if (options.downloadTemplate) {
					setel = options.downloadTemplate;
				} else {
					setel = $('.template-download', el);
				}

				if (!setel || !setel[0]) {
					console.error("Invalid downloadTemplate");
					return $(el);
				}

				if (!setel.attr('id')) {
					var id = Orb.getUniqueId('up');
					setel.attr('id', id);
				} else {
					var id = setel.attr('id');
				}
				delete(options.downloadTemplate);
				options.downloadTemplateId = id;

				if (!options.filesContainer) {
					options.filesContainer = $(el).find('.files');
				}

				options.start = function() {
					// Dont stack error messes. Once you upload again, the old one disappears
					$(el).find('.error').remove();
					options.filesContainer.show();
				};

				// Same as default except added check for 'that' still exists
				if (options.forceSend) {
					options.add = function (e, data) {
						var that = $(this).data('blueimp-fileupload') ||
								$(this).data('fileupload'),
							options = that.options,
							files = data.files;
						$(this).fileupload('process', data).done(function () {
							that._adjustMaxNumberOfFiles(-files.length);
							data.maxNumberOfFilesAdjusted = true;
							data.files.valid = data.isValidated = that._validate(files);
							data.context = that._renderUpload(files).data('data', data);
							that._forceReflow(data.context);
							that._transition(data.context).done(
								function () {
									if ((that._trigger('added', e, data) !== false) &&
										(options.autoUpload || data.autoUpload) &&
										data.autoUpload !== false && data.isValidated) {
										data.submit();
									}
								}
							);
						});
					};
					options.done = function (e, data) {};
				} else {
					options.done = function (e, data) {
						var that = $(this).data('fileupload'),
							template,
							preview;

						// Means the widget is no longer visible (eg tab closed before upload finished)
						if (!that) {
							return;
						}

						if (data.context) {
							data.context.each(function (index) {
								var file = ($.isArray(data.result) &&
									data.result[index]) || {error: 'emptyResult'};
								if (file.error && that._adjustMaxNumberOfFiles) {
									that._adjustMaxNumberOfFiles(1);
								}
								that._transition($(this)).done(
									function () {
										var node = $(this);
										template = that._renderDownload([file])
											.css('height', node.height())
											.replaceAll(node);
										that._forceReflow(template);
										that._transition(template).done(
											function () {
												data.context = $(this);
												that._trigger('completed', e, data);
											}
										);
									}
								);
							});
						} else {
							template = that._renderDownload(data.result)
								.appendTo(that.options.filesContainer);
							that._forceReflow(template);
							that._transition(template).done(
								function () {
									data.context = $(this);
									that._trigger('completed', e, data);
								}
							);
						}
					};
				}

				// Same as default except added check for 'that' still exists
				options.stop = function (e) {
					var that = $(this).data('fileupload');
					if (!that) {
						return;
					}
					that._transition($(this).find('.fileupload-buttonbar .progress')).done(
						function () {
							$(this).find('.bar').css('width', '0%');
							that._trigger('stopped', e);
						}
					);
				},

				$el.on('click', '.remove-attach-trigger', function(ev) {
					// Ignore .delete as they may be items rendered with the page,
					// eg. the list handles delete of existing attachments on its own
					if ($(this).hasClass('delete')) {
						return;
					}
					ev.preventDefault();

					var clicked = $(this), li = clicked.closest('li');
					li.slideUp('fast', function() {
						clicked.remove();

						if (options.filesContainer.hasClass('dp-hide-empty')) {
							options.filesContainer.hide();
						}
					});

					el.trigger('fileremoved', [li]);
				}).on('fileuploadfailed', function(e, data) {
					if (data.errorThrown == "Request Entity Too Large") {
						$(el).find('.error').remove();
						$(el).find('.files').append('<li class="error">The file you are trying to upload is too big.</li>');
					}
				});

				// drop could have an auth, which we handle manually (ie not fileupload jquery plugin)
				$el.on('drop', function(event) {

					// handle already uploaded blob
					var blobData = event.originalEvent.dataTransfer.getData('blobData');
					if (blobData) {
						blobData = JSON.parse(blobData);
						if (!blobs[blobData.blob_id]) {
							blobs[blobData.blob_id] = blobData;
							$(this).trigger('fileuploadstart');
							return options.done.apply(plainEl, [event, {result: [blobData]}]);
						}
					}

					var auth = event.originalEvent.dataTransfer.getData('DpAuthId');
					if (!auth) return;

					// Need slight delay to make sure an attached redactor editor isnt handling this
					window.setTimeout((function() {
						if (event.originalEvent.__DpIsRteHandling) {
							return;
						}

						$(this).trigger('fileuploadstart');
						options.start.apply(plainEl, [event]);

						$.ajax({
							url: options.url,
							dataType: 'html',
							data: { copy_blob: auth },
							cache: false,
							type: 'POST',
							success: function (data) {
								var json = $.parseJSON(data);

								$(this).trigger('fileuploaddone');
								if (typeof json.error == 'undefined') {
									options.done.apply(plainEl, [event, { result: json}])
								} else {
									options.stop.apply(plainEl, [event]);
								}

							}
						});
					}).call(this), 100);
				});

				$el.on('fileuploaddone', function(e, data){
					if (!data || !data.result || !data.result.length) return;

					for (var i = 0; i < data.result.length; i++) {
						var blob = data.result[i];
						if (blob.blob_id) {
							blob.filelink = blob.download_url; // used in RteEditor
							blobs[blob.blob_id] = blob;
						}
					}
				});

				$el.on('dragstart', function(e){
					var id = $(e.target).data('blob-id');
					if (id && blobs[id]) {
						e.originalEvent.dataTransfer.setData('blobData', JSON.stringify(blobs[id]));
					}
				});

				$el.on('blobremove', function(e, id){
					blobs[id] && delete blobs[id];
				});

				return $(el).fileupload(options);
			},

			filedownload: function(el) {
				if (!el.is('.dragout')) {
					el = el.find('.dragout');
				}
				el.on("dragstart", function(evt) {
					var blobAuthId = $(this).data('blob-authid');
					var fileDetails = $(this).data('downloadurl');
					if (!fileDetails) {
						fileDetails = $(this).attr('drag-to-download');
					}

					if (evt.dataTransfer) {
						evt.dataTransfer.setData("DownloadURL",fileDetails);
						if (blobAuthId) evt.dataTransfer.setData("DpAuthId", blobAuthId);
					} else {
						evt.originalEvent.dataTransfer.setData("DownloadURL",fileDetails);
						if (blobAuthId) evt.originalEvent.dataTransfer.setData("DpAuthId", blobAuthId);
					}
				});
			},

			updateUserEmailAddressDisplay: function(person_id, email) {
				var sel = $('b.pemail-' + person_id);
				var mode = 'chance';
				if (!email || !email.length) {
					mode = 'hide';
				}

				sel.each(function() {
					var el = $(this);

					var hideEl = el;
					if (el.data('hide') && el.data('hide') == '@parent') {
						hideEl = el.parent();
					}

					if (mode == 'chance') {
						hideEl.show();
						el.text(email);
					} else {
						el.text('');
						hideEl.hide();
					}
				});
			},

			reloadInterface: function() {
				$('#reload_overlay').show().on('click', function(ev) { ev.stopPropagation(); });
				window.location.reload(false);
			},

			inIframe: function () {
				try {
					return window.self !== window.top;
				} catch (e) {
					return true;
				}
			}
		};
	},

	initPage: function() {

		// All target=blanks need to null out window.opener
		$(document).on('click', 'a[target="_blank"]', function(ev) {
      // colorbox image previews from tickets
      // open an inline overlay
      if ($(this).hasClass('cboxElement')) {
        return;
      }

			ev.preventDefault();
			var o = window.open($(this).attr('href'));
			o.opener = null;
		});

		$('html').addClass('dp-window-focus');
		(function() {
			var hidden = "hidden";

			// Standards:
			if (hidden in document)
				document.addEventListener("visibilitychange", onchange);
			else if ((hidden = "mozHidden") in document)
				document.addEventListener("mozvisibilitychange", onchange);
			else if ((hidden = "webkitHidden") in document)
				document.addEventListener("webkitvisibilitychange", onchange);
			else if ((hidden = "msHidden") in document)
				document.addEventListener("msvisibilitychange", onchange);
			// IE 9 and lower:
			else if ('onfocusin' in document)
				document.onfocusin = document.onfocusout = onchange;
			// All others:
			else
				window.onpageshow = window.onpagehide
					= window.onfocus = window.onblur = onchange;

			function onchange (evt) {
				var v = 'visible', h = 'hidden', evtMap = { focus:v, focusin:v, pageshow:v, blur:h, focusout:h, pagehide:h }, changedTo;

				evt = evt || window.event;
				if (evt.type in evtMap) {
					changedTo = evtMap[evt.type];
				} else {
					changedTo = this[hidden] ? "hidden" : "visible";
				}

				if (changedTo == 'hidden') {
					$('html').addClass('dp-window-nonfocus').removeClass('dp-window-focus');
				} else {
					$('html').addClass('dp-window-focus').removeClass('dp-window-nonfocus');
				}
			}
		})();

		var startHash = window.location.hash + "";
		startHash = startHash.substring(1);

		this.startRestoreHash = '';
		if (!startHash.length && Modernizr.localstorage && localStorage['last_state']) {
			startHash = localStorage['last_state'];
			this.startRestoreHash = startHash;
		}

		var loadNewTicket = false;
		if (loadNewTicket = window.location.hash.match(/#newticket:(\d+)/)) {
			loadNewTicket = loadNewTicket[1];
		}

		var loadSearchTerm;
		if (loadSearchTerm = window.location.hash.match(/#q:(.*?)$/)) {
			loadSearchTerm = loadSearchTerm[1];
		}

		var loadVis;
		if (loadVis = window.location.hash.match(/vis:([0-9]{1})/)) {
			loadVis = parseInt(loadVis[1]);
		}
		if ($('html').hasClass('ipad') || $('html').hasClass('iphone')) {
			loadVis = 2;
			this.isMobile = true;
			this.setPaneVisNum(loadVis);
		}
		if (!loadVis && Modernizr.localstorage && window.localStorage['dp_vis']) {
			loadVis = parseInt(window.localStorage['dp_vis']) || 7;
			this.setPaneVisNum(loadVis);
		}

		var loadAdmin;
		if (loadAdmin = window.location.hash.match(/#admin:(.*?)$/)) {
			loadAdmin = loadAdmin[1];
		}

		var loadReports;
		if (loadReports = window.location.hash.match(/#reports:(.*?)$/)) {
			loadReports = loadReports[1];
		}

		var loadReportsInterface;
		if (loadReportsInterface = window.location.hash.match(/#reports-interface:(.*?)$/)) {
			loadReportsInterface = loadReportsInterface[1];
		}

		if (this.util.inIframe()) {
			console.log('Sending iframe message');
			data = {
				reload: true,
				location: window.location.href
			};
			window.top.postMessage(data, window.location.origin);
		}

		$.fn.qtip.zindex = 999999999;
		if (!$('html').hasClass('browser-ie')) {
			// Prevents default browser action of navigating to a dropped file
			// if a drop target isnt configured yet (ie no tab open to accept a file)
			$(document).bind('drop dragover', function (e) {
				e.preventDefault();
			});

			$(document).bind('dragover', function (e) {
				var timeout = window.dropZoneTimeout;
				if (!timeout) {
					$('body').addClass('file-drag-over');
				} else {
					clearTimeout(timeout);
				}

				window.dropZoneTimeout = setTimeout(function () {
					window.dropZoneTimeout = null;
					$('body').removeClass('file-drag-over');
				}, 100);
			});
		}

		$('html').addClass('dp');
		this._initLayout();
		this._initWindowInterface();
		this._initBasic();
		this._initRoutes();
		this._initSections();
		this._initInterfaceServices();

    this.ticketSnippetDriver = new DeskPRO.Agent.TextSnippetAjaxDriver('tickets');
    this.chatSnippetDriver   = new DeskPRO.Agent.TextSnippetAjaxDriver('chat');

		if (window.devicePixelRatio && window.devicePixelRatio >= 2) {
			$('body').addClass('dp-is-retina');
		}

		if (!loadAdmin && !loadReports && !loadReportsInterface) {
			$('#page_loading').remove();
			$('#loading_css').remove();
		}

		if (!window.DeskPRO_FragmentRouter) {
			DP.console.warn('window.DeskPRO_FragmentRouter is missing. Using empty router.');
			window.DeskPRO_FragmentRouter = {
				baseUrl: '',
				setBaseUrl: function(x) { this.baseUrl = x; },
				hasFragment: function() { return false; },
				getFragmentPattern: function() { return ''; },
				getFragmentType: function() { return ''; },
				getUrl: function() { return ''; },
				getUrlNamedArgs: function() { return ''; }
			};
		}

		this.fragmentRouter = window.DeskPRO_FragmentRouter;
		this.fragmentRouter.setBaseUrl(BASE_URL);

		var self = this;
		this.hashInitial = false;

		$.history.init(function(hash){
			if (!self.hashInitial) {
				self.hashInitial = true;
				return;
			}
			self.loadHashPath(hash);
			if (Modernizr.localstorage) {
				localStorage['last_state'] = hash;
			}
		},{ unescape: ",/:" });

		if (!this.openSection) {
			this.switchToSection($('#dp_nav [data-section-handler]').first().attr('id'));
		}

		this.messageChanneler.poller.send();

		var fn;
		while (fn = this.onloadStack.shift()) {
			fn();
		}

		$(document).on('click', '.click-confirm', function(ev) {
			if (!confirm($(this).data('confirm'))) {
				ev.preventDefault();
			}
		});

		// Used by the poller to send flag to update the last active time
		$(document).on('click mousemove keypress', function() {
			self.activityTime = new Date();
		});

		$('#dp_tab_list_btn').on('click', function(ev) {
			Orb.cancelEvent(ev);
			var $menu = $('#dp_tab_list_menu');
			var $me = $(this);
			var pos = $me.offset();

			$me.addClass('active').parent().addClass('active');

			$menu.css({
				top: pos.top + 23,
				left: pos.left + 1
			}).show();

			var closeFn = function() {
				$me.removeClass('active').parent().removeClass('active');
				$menu.hide();
			};

			$menu.on('click', function() { closeFn(); Orb.shimClickCallbackPop(); });
			Orb.shimClickCallback(closeFn, 'zindex-chrome0');
		});
		$('#dp_tab_list_menu').detach().appendTo('body');

		var isIe = $('html').hasClass('browser-ie');

		$('#dp_header_help_trigger').on('click', function(ev) {
			ev.preventDefault();

			var wrap = $('#dp_header_help');
			wrap.addClass('active');

			var closeFn = function() {
				wrap.removeClass('active');
			};

			if (!wrap.data('has-init')) {
				wrap.find('.btn-menu').on('click', function(ev) {
					Orb.cancelEvent(ev);
					Orb.shimClickCallbackPop();
				});
			}

			Orb.shimClickCallback(closeFn, 'zindex-chrome0');
		});

		if (DP_PERSON_PASSWORD_EXPIRED) {
			var settingsInterval = setInterval(function() {
				if (window.SETTINGS_WINDOW) {
					clearInterval(settingsInterval);
					settingsInterval = false;
					$('#settingswin').trigger('dp_open');
				}
			}, 250);
		} else {
			if (loadAdmin) {
				this.disableHashPath(function () {
				});
				if ($('#admin_interface_trigger').data('handler')) {
					console.log("Loading admin: " + loadAdmin);
					$('#admin_interface_trigger').data('handler').open(loadAdmin, function () {
						$('#page_loading').remove();
						$('#loading_css').remove();
					});
				}
			} else if (loadReports) {
				this.disableHashPath(function () {
				});
				if ($('#reports_interface_trigger').data('handler')) {
					console.log("Loading reports: " + loadReports);
					$('#reports_interface_trigger').data('handler').open(loadReports, function () {
						$('#page_loading').remove();
						$('#loading_css').remove();
					});
				}
			} else if (loadReportsInterface) {
				this.disableHashPath(function () {
				});
				if ($('#reports2_interface_trigger').data('handler')) {
					console.log("Loading reports: " + loadReportsInterface);
					$('#reports2_interface_trigger').data('handler').open(loadReportsInterface, function () {
						$('#page_loading').remove();
						$('#loading_css').remove();
					});
				}
			} else {
				if (loadNewTicket) {
					DeskPRO_Window.newTicketLoader.open(function (page) {
						var data = {
							person_id: loadNewTicket
						};
						page.setNewByPerson(data);
					});
				}

				if (loadSearchTerm) {
					$('#dp_search_box').focus().val(decodeURIComponent(loadSearchTerm)).trigger('keypress');
				}

				if (loadVis) {
					this.layout.enableHashUpdate = false;
					this.setPaneVisNum(loadVis);
					this.layout.enableHashUpdate = true;
				}

				this.cancelHashLoad = 0;
				this.loadHashPath(startHash);
			}
		}

		$('#agents_section').on('click', function(ev) {
			if (window['DP_FRAME_OVERLAYS']) {
				for (var k in window['DP_FRAME_OVERLAYS']) {
					if (window['DP_FRAME_OVERLAYS'].hasOwnProperty(k)) {
						window['DP_FRAME_OVERLAYS'][k].close();
					}
				}
			}
		});

		$(document).on('dragover', 'ul.dp-tab-list > li', function(e){
			if (!$(this).hasClass('activeTabList')) {
				$(this).trigger('click');
			}
		});


		/****************** new_ticket drafts ***************/
		for (var i = 0; i < window.localStorage.length; i++){
			var key = window.localStorage.key(i);
			if ('drafts.new-ticket-' !== key.substr(0, 18)) continue;

			(function(key){
				DeskPRO_Window.runPageRoute('page:' + BASE_URL + 'agent/tickets/new', {
					openCallback: function(page) {
						if (page.draft) {
              page.draft._key = key;
              page.draft.load();
						}
					},
					ignoreExist: true
				});
			})(key);
		}

    window.document.addEventListener('dpCloseOverlayFrame', function (e) {
      if (e.detail.id === 'admin') {
				if (window.DP_NEED_RELOAD == true) {
					DeskPRO_Window.showRefreshAlert();
				}
			}
		});


		/***************** scrolling handle on drag ******************/
		var drag = function(){
			var d = {
				timer: null,
				started: false,
				wrap: $('#dp_content_wrap'),
				offset: 50,
				step: 20,
				interval: 50
			};

			d.start = function($c, direction){
				if (d.timer) return false;
				direction = direction || 1;
				direction = direction > 0 ? '+=' : '-=';
				d.timer = setInterval(function(){ $c.scrollTo(direction + d.step + 'px'); }, d.interval);
			};
			d.stop = function(){
				d.timer && clearInterval(d.timer);
				d.timer = null;
			};

			return d;
		}();

		$(document).on('dragstart', '.dp-page-content', function(){
			drag.started = true;
		});
		$(document).on('dragend', function(){
			drag.stop();
			drag.started = false;
		});
		$(document).on('dragover', function(e){
			if (!drag.started) return;
			var y = e.originalEvent.y,
				$c = $('.dp-page-content').parent().parent();

			if (y < drag.wrap.offset().top + drag.offset) {
				drag.start($c, -1);
			} else if ( y > drag.wrap.offset().top + drag.wrap.height() - drag.offset ) {
				drag.start($c, 1);
			} else {
				drag.stop();
			}
		});
		/***************** /scrolling handle on drag ******************/

		// after page is loaded, lets do render on init
		// this means loading a page enables lazy loading
		// of tabs from url bar or local history, but
		// actually clicking stuff will instant
    DeskPRO_Window.TabBar.enableInitOnRender();
	},

	initScope: function() {
		var $scope = this.$scope,
			self = this;

		$scope.paneVis = this.paneVis;
		$scope.listItems = [];
		self._last = 'list';

		$scope.$watch('paneVis', function(newVal, oldVal){
			self.layout.doResize(true);
		}, true);

		$scope.oneColumnView = function() {
			if (!(this.paneVis.list && this.paneVis.tabs)) return;
			this.paneVis.list = false;
			this.paneVis.tabs = false;
			this.paneVis[self._last] = true;
		};

		$scope.twoColumnsView = function() {
			if (this.paneVis.list && this.paneVis.tabs) return;
			self.paneVis.list = true;
			self.paneVis.tabs = true;
		};

		$scope.highlightIdentity = function(identity) {
			$scope.removeHighlight();
			var identityClass = identity.replace(':', '-');
			$('.row-item.' + identityClass).addClass('item-hover-over');
			$('#tabNavigationPane .' + identityClass).addClass('item-hover-over');
		};

		$scope.removeHighlight = function() {
			$('.item-hover-over').removeClass('item-hover-over');
		};

		$scope.showList = function() {
			$scope.$safeApply(function(){
				if (!(self.paneVis.list && self.paneVis.tabs)) { // if 1 column mode
					self.paneVis.tabs = false;
				}
				self.paneVis.list = true;
				self._last = 'list';
			});
		};

		$scope.showTabs = function() {
			$scope.$safeApply(function(){
				if (!(self.paneVis.list && self.paneVis.tabs)) { // if 1 column mode
					self.paneVis.list = false;
				}
				self.paneVis.tabs = true;
				self._last = 'tabs';
			});
		};

		$scope.toggleSourcePane = function() {
			$scope.$safeApply(function() {
				self.setPaneVis('source', !self.paneVis.source);
			});
		};

		$scope.addListItem = function(type, identity, title, route) {
			$scope.listItems.push({type: type, identity: identity, title: title, route: route});
		};
	},

	initAppPlatform: function(AppPlatform) {

		var self = this;

		if (this.AppPlatform) return;
		this.AppPlatform = AppPlatform;
		this.AppPlatform.start();

		this.ngModule = this.AppPlatform.getNgModule();
		this.ngModule.dpInjector = window.AppPlatform.getNgInjector();

		// injector required at init stage, as AppPlatform initiated after all $scope vars filled
		window.AppPlatform.getNgInjector().invoke(['$rootScope', '$q', '$timeout', '$http', function($rootScope, $q, $timeout, $http) {
			self.$scope = $rootScope;
			self.$q = $q;
			self.$timeout = $timeout;
			self.$http = $http;

			self.$scope.$safeApply = (function(fn) {
				var phase = this.$root.$$phase;
				if(phase == '$apply' || phase == '$digest') {
					if(fn && (typeof(fn) === 'function')) {
						fn();
					}
				} else {
					this.$apply(fn);
				}
			}).bind(self.$scope);
		}]);
		this.initScope();
	},

	getAppPlatform: function() {
		return this.AppPlatform || null;
	},

	addOnloadFunction: function(fn) {
		this.onloadStack.push(fn);
	},

	disableHashPath: function(custom_handler) {
		this.hashHandling = false;
		this.customHashHandler = custom_handler;
	},

	enableHashPath: function() {
		this.hashHandling = true;
		this.cancelHashLoad = 0;
		this.customHashHandler = null;
	},

	loadHashPath: function(browserHash) {

		if (this.customHashHandler) {
			this.customHashHandler(browserHash);
		}

		if (!this.hashHandling) return;
		if (this.DEBUG.disableUrlFragments) return;

		// This is sometimes set to prevent any of the below loading
		// to happen when the hash is updated to reflect an already-set
		// URL state
		if (this.cancelHashLoad > 0) {
			this.cancelHashLoad--;
			if (this.cancelHashLoad < 0) {
				this.cancelHashLoad = 0;
			}
			return;
			}

		if (!browserHash.length) {
			return;
		}

		if (browserHash.indexOf('%') !== -1) {
			browserHash = decodeURIComponent(browserHash);
		}

		// Hashes are #keyword.tabid:arg1:arg2
		// tabid part is for non-unique pages (ie newticket) and
		// a user is clicking between tabs. It is optional,
		// and ignored if the tabid doesn't exist.

		// Hash segments are separated by commas. Each segment is a different
		// page. The first segment should be the list pane, but this is enforced
		// anyway by the fragment_type in routing.yml

		var segments = browserHash.split(',');
		var activateSection = null;
		var activateTabId = null;
		var firstTabId = null;
		var activateSettings = null;
		var startRestoreHash = this.startRestoreHash || '';
		var tabsToOpen = [];
		var openTabsLimit = 20;

		DeskPRO_Window.TabBar.options.activateNew = false;

		Array.each(segments, function (hash, i) {

			var m;
			if (m = hash.match(/app\.([a-zA-Z]+)/)) {
				activateSection = m[1];
				return;
			}

			if (m = hash.match(/settings\.([a-zA-Z0-9-]+)/)) {
				activateSettings = m[1];
				return;
			}

			// Active tab has .o on it, like ticket.o:1234
			// So detect that, and then remove the .o
			var isOpen = false;
			if (hash.match(/\.o:/)) {
				isOpen = true;
				hash = hash.replace(/\.o:/, ':');
			}

			var tabId = DeskPRO_Window.TabBar.findTabByFragment(hash);
			if (tabId) {
				tabId = tabId.id;
				if (isOpen) {
					activateTabId = tabId;
				} else if (!firstTabId) {
					firstTabId = tabId;
				}
				return;
			}

			var listPage = this.getCurrentListPage();
			if (listPage && listPage.getMetaData('url_fragment') == hash) {
				return;
			}

			var parts = hash.match(/^(.*?)(\.(.*?))?:(.*?)$/);

			if (!parts) {
				// Invalid
				return;
			}

			var tabId = null;
			if (parts[3]) {
				var tabId = parts[3];
			}

			var fragmentName = parts[1];
			var args = parts[4];

			if (!args.length) {
				args = [];
			} else {
				args = args.split(':');
			}

			if (!this.fragmentRouter.hasFragment(fragmentName)) {
				return;
			}

			var argRequired = false;
			switch (fragmentName) {
				case 'knowledgebase':
				case 'news':
				case 'downloads':
				case 'guides':
				case 'category':
				case 'status':
				case 'label':
				case 'ended':
					argRequired = true;
					break;
			}

			if (argRequired && !args.length) {
				return;
			}

			var url = this.fragmentRouter.getUrl(fragmentName, args);
			var type = this.fragmentRouter.getFragmentType(fragmentName);

			if (type == 'vis') {
			} else if (type == 'list') {
				this.loadingListFragment = hash;
				this.loadListPane(url, { url_fragment: hash });
			} else {
				tabsToOpen.push({hash: hash, url: url});
			}
		}, this);

		tabsToOpen = tabsToOpen.slice(-openTabsLimit);
		for (var i = 0; i < tabsToOpen.length; i++) {
			var hash = tabsToOpen[i].hash,
					url = tabsToOpen[i].url;
			this.loadingPageFragment = hash;
			this.loadPage(url, { url_fragment: hash, noToggle: true, ignore_perm_error: startRestoreHash.replace(/\.o/, '').indexOf(hash.replace(/\.o/, '')) != -1 });
		}

		this.cancelHashLoad++;
		if (activateTabId) {
			DeskPRO_Window.TabBar.activateTabById(activateTabId);
		} else if (firstTabId) {
			DeskPRO_Window.TabBar.activateTabById(firstTabId);
		}

		if (activateSection) {
			var activateSectionId = null;
			Object.each(this.sections, function(section, id) {
				if (section.urlFragmentName && section.urlFragmentName == activateSection) {
					activateSectionId = id;
					return false;
				}
			});

			if (activateSectionId) {
				this.fragLoadingSection = activateSectionId;
				this.switchToSection(activateSectionId);
			}
		}

		if (activateSettings) {
			var settingsInterval = setInterval(function() {
				if (window.SETTINGS_WINDOW) {
					clearInterval(settingsInterval);
					settingsInterval = false;
					$('#settingswin').trigger('dp_open', activateSettings);
				}
			}, 250);
		}

		DeskPRO_Window.TabBar.options.activateNew = true;
	},

	updateWindowUrlFragmentNow: function() {

		if (!this.hashHandling) return;
		if (this.DEBUG.disableUrlFragments) return;
		if (!jQuery.history) return;

		var segments = [];

		if (this.openSection) {
			if (this.openSection.urlFragmentName) {
				segments.push('app.' + this.openSection.urlFragmentName);
			}
			if (this.openSection.listPage && this.openSection.listPage.getMetaData('url_fragment')) {
				segments.push(this.openSection.listPage.getMetaData('url_fragment'));
			}
		}

		if (DeskPRO_Window.TabBar) {			// Only if we have current tab, cuz no current tab means there are no tabs open at all

			for (var id in this.TabBar.tabs ) {
				var tab = this.TabBar.tabs[id];

				if (tab.page && tab.page.getMetaData('url_fragment')) {
					var tabPage = tab.page;
					var hash = tabPage.getMetaData('url_fragment');

					if (tab.isActive) {
						if (hash.indexOf(':') !== -1) {
							// ticket:123 to ticket.o:123
							hash = hash.replace(/:/, '.o:');
						} else {
							// somename to somename.o
							hash = hash + '.o';
						}
					}

					segments.push(hash);
				}
			};
		}

		var paneVisNum = this.getPaneVisNum();
		if (paneVisNum) {
			segments.push('vis:'+paneVisNum)
		}
		if (Modernizr.localstorage) {
			window.localStorage['dp_vis'] = paneVisNum || 7;
		}

		var browserHash = segments.join(',');

		this.cancelHashLoad++;
		jQuery.history.load(browserHash);
	},

	//#################################################################
	//# Global registry, getters
	//#################################################################

	/**
	 * Get the ID of the last client message.
	 */
	getLastClientMessageId: function() {
		if (this.messageChanneler.lastMessageId) {
			return this.messageChanneler.lastMessageId;
		}

		return 0;
	},

	forwardClientMessageData: function(data) {
		if (this.messageChanneler.handleMessageAjax) {
			this.messageChanneler.handleMessageAjax(data);
		}
	},


	/**
	 * Get the AJAX poller
	 */
	getPoller: function() {
		return this.messageChanneler.poller;
	},

	/**
	 * Get the tab watcher
	 */
	getTabWatcher: function() {
		return this.tabWatcher;
	},

	/**
	 * Get the tab strip
	 */
	getTabStrip: function() {
		return DeskPRO_Window.TabBar;
	},

	/**
	 * Get a name for some type of basic thing (department, category etc).
	 */
	getDisplayName: function(type, id) {
		if (!window.DESKPRO_NAME_REGISTRY[type] || !window.DESKPRO_NAME_REGISTRY[type][id]) {
			if (!window.DESKPRO_NAME_REGISTRY[type]) {
				DP.console.warn('Unknown name type %s', type);
			}

			return null;
		}

		return window.DESKPRO_NAME_REGISTRY[type][id];
	},


	/**
	 * Get information about an agent like their name, initials, picture URL.
	 *
	 * @param {Integer} agent_id
	 * @return {Object}
	 */
	getAgentInfo: function(agent_id) {
		// offline list always has all agents and their info
		var agentEl = $('#agent_offline_list .agent-' + agent_id);

		if (!agentEl.length) {
			DP.console.warn('Unknown agent %i', agent_id);
			return null;
		}

		return {
			id: agent_id,
			name: agentEl.data('agent-name'),
			email: agentEl.data('email'),
			shortName: agentEl.data('agent-short-name'),
			pictureUrl: agentEl.data('picture-url'),
			pictureUrlSizable: agentEl.data('picture-url-sizable')
		};
	},

	/**
	 * Get a URL pattern
	 */
	getUrl: function(name, vars) {
		if (!window.DESKPRO_URL_REGISTRY[name]) {
			DP.console.error('Unknown url name %s', name);
			return null;
		}

		var url = window.DESKPRO_URL_REGISTRY[name];
		if (vars) {
			Object.each(vars, function(v,k) {
				url = url.replace('{'+k+'}', v);
			});
		}

		return url;
	},


	/**
	 * Get data
	 * @param name
	 */
	getData: function(name) {
		if (!window.DESKPRO_DATA_REGISTRY || !window.DESKPRO_DATA_REGISTRY[name]) {
			DP.console.error('Unknown data name %s', name);
			return null;
		}

		return window.DESKPRO_DATA_REGISTRY[name];
	},


	//#################################################################
	//# Simple UI features
	//#################################################################

	showAlert: function(msg, classname) {
		this._initAlertOverlay();

		if (typeof msg == 'string') {
			var msgEl = $('<div/>');
			msgEl.text(msg);
			msg = msgEl;
		}

		$('#alert_overlay_msg').empty().append(msg);

		var wrapper = this._alertOverlay.elements.wrapperOuter;
		var wrapperModel = this._alertOverlay.elements.modal;
		if (wrapper.data('added-class')) {
			wrapper.removeClass(wrapper.data('added-class'));
			wrapperModel.removeClass(wrapper.data('added-class'));
			wrapper.data('added-class', null);
		}
		if (classname) {
			wrapper.addClass(classname);
			wrapperModel.addClass(classname);
			wrapper.data('added-class', classname);
		}

		this._alertOverlay.openOverlay();
	},

	showConfirm: function(msg, callback_yes, callback_no, phrase_yes, phrase_no, w, h) {
		this._initConfirmOverlay();

		w = w || 350;
		h = h || 150;

		phrase_yes = phrase_yes || 'Okay';
		phrase_no = phrase_no || 'Cancel';

		this._confirmOverlay_callback_yes = callback_yes || function() { };
		this._confirmOverlay_callback_no = callback_no || function() { };

		$('#confirm_overlay').find('> .confirm-overlay').width(w).height(h);

		$('#confirm_overlay_msg').html(msg);
		$('#confirm_overlay .okay-trigger').text(phrase_yes);

		if (phrase_no == 'hidden') {
			$('#confirm_overlay .cancel-trigger').text(phrase_no).hide();
		} else {
			$('#confirm_overlay .cancel-trigger').text(phrase_no).show();
		}

		this._confirmOverlay.openOverlay();
	},

	showPrompt: function(msg, callback_ok, callback_cancel) {
		this._initPromptOverlay();

		this._promptOverlay_callback_yes = callback_ok || function() { };
		this._promptOverlay_callback_no = callback_cancel || function() { };

		$('#prompt_overlay_msg').html(msg);
		this._promptOverlay.openOverlay();
	},

	_initAlertOverlay: function() {
		if (this._alertOverlay) return;

		this._alertOverlay = new DeskPRO.UI.Overlay({
			zIndex: 'none', // the .window-alert sets the zindex
			contentElement: $('#alert_overlay'),
			customClassname: 'window-alert',
			onContentSet: function(eventData) {
				$('.close-trigger', eventData.wrapperEl).on('click', (function() {
					eventData.overlay.closeOverlay();
				}).bind(this));
			}
		});

		// Need to init it now because showAlert will try to set a
		// class on it sometimes, and we need the elements ready
		this._alertOverlay.initOverlay();
	},

	_initConfirmOverlay: function() {
		if (this._confirmOverlay) return;

		this._confirmOverlay_callback_yes = function() { };
		this._confirmOverlay_callback_no = function() { };

		var self = this;

		this._confirmOverlay = new DeskPRO.UI.Overlay({
			contentElement: $('#confirm_overlay'),
			zIndex: '50000',
			onContentSet: function(eventData) {
				$('.cancel-trigger', eventData.wrapperEl).on('click', (function() {
					eventData.overlay.closeOverlay();
					self._confirmOverlay_callback_no();
					self._confirmOverlay_callback_no = function() {};
				}).bind(this));
				$('.okay-trigger', eventData.wrapperEl).on('click', (function() {
					eventData.overlay.closeOverlay();
					self._confirmOverlay_callback_yes();
					self._confirmOverlay_callback_yes = function() {};
				}).bind(this));
			}
		});
	},

	_initPromptOverlay: function() {
		if (this._promptOverlay) return;

		this._promptOverlay_callback_yes = function() { };
		this._promptOverlay_callback_no = function() { };

		var self = this;

		this._promptOverlay = new DeskPRO.UI.Overlay({
			contentElement: $('#prompt_overlay'),
			zIndex: 'top',
			onContentSet: function(eventData) {
				$('.cancel-trigger', eventData.wrapperEl).on('click', (function() {
					eventData.overlay.closeOverlay();
					self._promptOverlay_callback_no($('#prompt_overlay_input').val(), $('#prompt_overlay'));
					self._promptOverlay_callback_no = function() {};
					$('#prompt_overlay_input').val('');
				}).bind(this));
				$('.okay-trigger', eventData.wrapperEl).on('click', (function() {
					eventData.overlay.closeOverlay();
					self._promptOverlay_callback_yes($('#prompt_overlay_input').val(), $('#prompt_overlay'));
					self._promptOverlay_callback_yes = function() {};
					$('#prompt_overlay_input').val('');
				}).bind(this));
			}
		});
	},

	showRefreshAlert: function(admin_name, message, allowIgnore) {

		if (typeof allowIgnore == 'undefined') {
      allowIgnore = true;
		}

		var self = this;

		if (this._refreshAlertTimeout) {
			window.clearTimeout(this._refreshAlertTimeout);
			this._refreshAlertTimeout = null;
		}

		if (!this._refreshAlertOverlay) {
			this._refreshAlertOverlay = new DeskPRO.UI.Overlay({
				contentElement: $('#refresh_alert_overlay'),
				zIndex: '50000',
				escapeClose: false,
        addClose: false,
				modalClickClose: false,
        customClassname: 'refresh-alert'
			});

			$('#refresh_alert_overlay').find('button.okay-trigger').on('click', function(ev) {
				ev.preventDefault();
				ev.stopPropagation();

				if (self._refreshAlertTimeout) {
					window.clearTimeout(self._refreshAlertTimeout);
					self._refreshAlertTimeout = null;
				}
				window.location.reload(false);
			});
			$('#refresh_alert_overlay').find('button.cancel-trigger').on('click', function(ev) {
				ev.preventDefault();
				ev.stopPropagation();

				if (self._refreshAlertTimeout) {
					window.clearTimeout(self._refreshAlertTimeout);
					self._refreshAlertTimeout = null;
				}

				self._refreshAlertOverlay.closeOverlay();
			});
		}

		if (admin_name) {
			$('#refresh_alert_overlay').find('.admin-name').text(admin_name);
		} else {
			var overlay = $('#refresh_alert_overlay');
			overlay.find('.by_admin').hide();
			overlay.find('.self').show();
		}

		if (message) {
      $('#refresh_alert_overlay').find('.refresh_message').text(message).show();
		} else {
      $('#refresh_alert_overlay').find('.refresh_message').text(message).hide();
		}

		if (allowIgnore) {
			$('#refresh_alert_overlay').find('.cancel-trigger').show();
		} else {
      $('#refresh_alert_overlay').find('.cancel-trigger').hide();
		}

		var time = 30;
		var timeShow = $('#refresh_alert_overlay').find('.countdown').text(30);

		this._refreshAlertTimeout = window.setInterval(function() {
			time--;
			$('#refresh_alert_overlay').find('.countdown').text(time);

			if (time <= 0) {
				if (self._refreshAlertTimeout) {
					window.clearTimeout(self._refreshAlertTimeout);
					self._refreshAlertTimeout = null;
				}

				window.location.reload(false);
			}
		}, 1000);

		this._refreshAlertOverlay.openOverlay();
	},

	//#################################################################
	//# Routes and page loading
	//#################################################################

	addListPage: function(page) {
		DP.console.warn('Invalid call to addListPage for %o', page);
		this.setListPage(page);
	},

	setListPage: function(page, noswitch) {

		// Route a list page fragment into the proper
		// section

		var testcl = function(x) {
			return page.getMetaData('fragmentClass', '').indexOf(x) != -1;
		};
		var handler = null;
		var sectionId = null;
		if (testcl('.Kb') || testcl('.News') || testcl('.Download') || testcl('.Publish') || testcl('PublishSearch') || testcl('.Guide')) {
			sectionId = 'publish_section';
		} else if (testcl('.Ticket') || testcl('.NewCustomFilter')) {
			sectionId = 'tickets_section';
		} else if (testcl('.People') || testcl('.Org')) {
			sectionId = 'people_section';
		} else if (testcl('.AgentChat')) {
			sectionId = 'agent_chat_section';
		} else if (testcl('.OpenChats') || testcl('.UserChatFilter')) {
			sectionId = 'chat_section';
		} else if (testcl('.Feedback') || testcl('.FeedbackSearch')) {
			sectionId = 'feedback_section';
		} else if (testcl('.TicketFilter') || testcl('.RecycleBin')) {
			sectionId = 'tickets_section';
		} else if (testcl('.Task')) {
			sectionId = 'tasks_section';
		}

		if (sectionId) {
			handler = this.sections[sectionId];
		}

		if (!handler) {
			DP.console.warn('List page fragment has no section: %s: %o', page.getMetaData('fragmentClass', ''), page);
			return;
		}

		if (typeof noswitch === undefined) {
			if (!noswitch && !handler.isVisible()) {
				noswitch = true;
			}

			if (handler.isVisible() && !handler.listPage) {
				noswitch = false;
			}
		}

		handler.setListPageFragment(page, noswitch);

		if (!noswitch) {
			this.listPage = page;
			this.switchToSection(sectionId, true);
			this.updateWindowUrlFragment();
		}
	},

	getListPage: function() {
		return this.listPage;
	},

	addPageTab: function(page) {
		DeskPRO_Window.TabBar.addTab(page);
	},

	/**
	 * Checks views for a specific page and removes it
	 */
	removePage: function(page) {
		DeskPRO_Window.TabBar.removeTabById(page.meta.tabId);
	},

	/**
	 * Add a loader for a particular prefix.
	 *
	 * @param {String} prefix The prefix to lisen for. Eg "navpane:tickets"
	 * @param {Function} callback The function to call when the prefix is used
	 */
	addPageRouteLoader: function(prefix, callback) {
		if (this.routePrefixes[prefix] == undefined) {
			this.routePrefixes[prefix] = [];
		}

		this.routePrefixes[prefix].push(callback);
	},


  /**
   * Loads a route.
   *
   * @param {String} route The route to match, like navpane:tickets:filters
   * @param {object} extraData
   */
	runPageRoute: function(route, extraData) {
		var found_listener = false;

		var data = this.parseRoute(route);
		if (extraData) {
			data = Object.merge(extraData, data);
		}

		Object.each(this.routePrefixes, function(listeners, prefix) {
			if (route.indexOf(prefix) == 0) {
				Array.each(listeners, function(callback) {
					callback(data);
					found_listener = true;
				});
				if (data.stopListeners) {
					return true;
				}
			}
		}, this);

		if (!found_listener) {
			DP.console.error('Unknown route: %s', route);
		}
	},

	/**
	 * Parse a route into its parts
	 *
	 * @param {String} route
	 * @return {Object}
	 */
	parseRoute: function(route) {
		// Like:
		// master.masterTag:sectioninfo:moreinfo:url/here/at/end
		// (There might not be any sectioninfo)
		// Example:
		// listpane:/agent/ticket-search/filter/123

		var sections = route.split(':');
		var master = sections.shift();
		var masterTag = null;
		if (master.indexOf('.') != -1) {
			var tmp = master.split('.');
			master = tmp.shift();
			masterTag = tmp.pop();
		}

		var url = sections.pop();

		var data = {
			'route': route,
			'master': master,
			'masterTag': masterTag,
			'sections': sections,
			'url': url,
			stopListeners: false
		};

		return data;
	},



	/**
	 * Loads a route attached to an element. Useful for quickly assigning click events.
	 *
	 * @param {jQuery} el The element to inspect for a route
	 */
	runPageRouteFromElement: function(el, extraData) {

		el = $(el);

		if (el.is('.route-do-confirm')) {
			if (!el.is('.did-confirm')) {
				DeskPRO_Window.showConfirm('Are you sure?', function() {
					el.addClass('did-confirm');
					DeskPRO_Window.runPageRouteFromElement(el);
				});
				return;
			}
			el.removeClass('did-confirm');
		}

		if (el.is('.cancel-route')) {
			return;
		}

		if (!el.data('route')) {
			DP.console.warn('Element has no route: %o', el);
			DP.console.trace();
			return;
		}

		extraData = extraData || {};
		extraData.routeTriggerEl = el;
		if (el.data('route-title')) {
			extraData.title = el.data('route-title');
			if (extraData.title == '@text') {
				extraData.title = el.text().trim().replace(/[\n\r]/g, ' ').replace(/\s+/g, ' ');
			} else if (extraData.title == '@title') {
				extraData.title = el.attr('title');
			} else if (extraData.title.test(/^@selector\((.*?)\)$/)) {
				var sel = extraData.title.match(/^@selector\((.*?)\)$/)[1];
				var titleEl = null;
				if (sel[0] == "#") {
					titleEl = $(sel);
				} else {
					titleEl = $(sel, el);
				}

				if (titleEl && titleEl.length) {
					extraData.title = titleEl.text().trim().replace(/[\n\r]/g, ' ').replace(/\s+/g, ' ');
				} else {
					delete extraData.title;
				}
			}
		}
		if (el.data('route-openclass')) {
			extraData.toggleOpenClass = el.data('route-openclass');
		}

		if (el.data('route-notabreload')) {
			extraData.noToggle = true;
			extraData.focus = true;
		} else {
			if (el.closest('#dp_content_wrap')[0] || el.closest('.popover-wrapper')[0]) {
				extraData.noToggle = true;
				extraData.focus = true;
			}
		}

		if (el.data('route-preload-id')) {
			extraData.preloadId = el.data('route-preload-id');
		}

    if (!isShiftClick) {
      // this should be handled only when click event occurs
      if (0 === el.data('route').indexOf('listpane:')) {
        this.$scope.showList();
      } else {
        this.$scope.showTabs();
      }
    }

		if (!this.paneVis.tabs) {
			extraData.noToggle = true;
			extraData.focus = true;
		}

		if (el.data('route-replacetab')) {
			extraData.replaceTab = true;
		} else if (el.hasClass('row-item') && !this.paneVis.tabs) {
			extraData.replaceTab = true;
		}

		var isShiftClick = false;
		if (extraData.event && extraData.event.shiftKey) {
			isShiftClick = true;
		}

		if (el.data('route-newtab') || isShiftClick) {
			extraData.replaceTab = false;
			extraData.focus = false;

			if (isShiftClick) {
				extraData.noToggle = true;
			}
		}

		var popoverEl = el.closest('.popover-wrapper');
		if (popoverEl[0] && popoverEl.data('popover-handler')) {
			popoverEl.data('popover-handler').close(true);
		}

		this.backToAgent();
		this.runPageRoute(el.data('route'), extraData);
	},



	/**
	 * Load route data into the interface.
	 *
	 * @param {Object} routeData
	 */
	loadRoute: function(routeData) {

		routeData.openInSection = routeData.master;

		switch (routeData.openInSection) {
			case 'listpane':
				this.loadListPane(routeData.url, routeData);
				break;

			default:
				this.loadPage(routeData.url, routeData);
				if (this.isSingleColMode()) {
					$('#dp_omnibox').trigger('dpClose');
					Object.each(DeskPRO.Agent.PageHelper.Popover_Instances, function(i) {
						i.close();
					});
				}
				break;
		}
	},


	loadRouteOverlay: function(routeData) {

		var positionAbove = null;
		var zindex = 0;
		var trigger = routeData.routeTriggerEl;
		if (trigger) {
			if (trigger.data('zindex')) {
				zindex = trigger.data('zindex');
			} else {
				var parent = trigger.parentsUntil('body').last();
				if (parent.length && parent.parent().is('body')) {
					positionAbove = parent;
				}
			}
		}

		var fragmentOverlay = new DeskPRO.Agent.PageHelper.FragmentOverlay({
			routeData: routeData,
			positionAbove: positionAbove,
			zIndex: zindex
		});
	},


	/**
	 * Load a URL and treat it as a list pane.
	 *
	 * @param {String} url The URL of the list pane.
	 */
	loadListPane: function(url, routeData, callback) {

		if (routeData && !routeData.isBackgroundLoad) {
			if (this.loadingListPage) {
				this.loadingListPage.abort();
				this.loadingListPage = null;
			}
		}

		if (routeData && !routeData.isBackgroundLoad) {
			$('#dp_list > section').removeClass('on');
			$('#dp_list_loading').addClass('on');
		}
		var xhr = this._doAjaxLoadRoute(url, routeData, (function(data) {

			if (!routeData) {
				routeData = {};
			}
			if (routeData && !routeData.isBackgroundLoad) {
				this.loadingListPage = null;
			}

			$('#dp_list_loading').removeClass('on');

			var page = this.createPageFragment(data, 'DeskPRO.Agent.PageFragment.ListPane.Basic');

			page.setMetaData('routeUrl', url);
			if (routeData) {
				page.setMetaData('routeData', routeData);
			}

			this.setListPage(page, routeData.isBackgroundLoad || false);

			if (callback) callback(page);
		}).bind(this));

		if (routeData && !routeData.isBackgroundLoad) {
			this.loadingListPage = xhr;

			if (window.DP_CLOSE_SEARCH) {
				window.DP_CLOSE_SEARCH();
			}
		}
	},



	/**
	 * Load a URL and treat and put it into the tabbed pane.
	 *
	 * @param {String} url The URL of the page
	 */
	loadPage: function(url, routeData, callback) {
		var self = this;
		if (!routeData || (!routeData.ignoreExist)) {
			var existTab = DeskPRO_Window.TabBar.findTabByRouteUrl(url);
			if (existTab && (routeData.noToggle || routeData.replaceTab)) {
				if (routeData.focus || routeData.replaceTab) {
					DeskPRO_Window.TabBar.activateTab(existTab);
				}
				return;
			}
			if (existTab && !(existTab.page.allowDupe && existTab.page.TYPENAME != 'loading')) {
				if(routeData && routeData.routeTriggerEl && routeData.routeTriggerEl.data('route-notabreload')) {
					DeskPRO_Window.TabBar.tabToFrontTabById(existTab.id);
					DeskPRO_Window.TabBar.activateTabById(existTab.id);
				} else {
					if (DeskPRO_Window.TabBar.currentTabId == existTab.id) {
						if (existTab.page && existTab.page.fireEvent) {
							event.deskpro = {cancelClose: false};
							existTab.page.fireEvent('closeTab', [event, existTab]);

							if (event.deskpro.cancelClose) {
								return;
							}
						}
						DeskPRO_Window.TabBar.removeTabById(existTab.id);
						if (routeData.routeTriggerEl && routeData.toggleOpenClass) {
							routeData.routeTriggerEl.removeClass(routeData.toggleOpenClass);
						}
					} else {
						DeskPRO_Window.TabBar.activateTab(existTab);
					}
				}
				return;
			}
		}

		var currentActiveTabId = DeskPRO_Window.TabBar.getActiveTabId();

		// Add a temporary tab to the tabstrip
		routeData.tabPlaceholderId = DeskPRO_Window.TabBar.addTabPlaceholder(url, routeData);

		if (currentActiveTabId && routeData && routeData.replaceTab) {
			var currentTab = DeskPRO_Window.TabBar.getTab(currentActiveTabId);
			if (!(currentTab && currentTab.page && currentTab.page.NO_REPLACE_TAB)) {
				DeskPRO_Window.TabBar.removeTabById(currentActiveTabId);
			}
		}

		if (routeData.routeTriggerEl && routeData.toggleOpenClass) {
			routeData.routeTriggerEl.addClass(routeData.toggleOpenClass);
		}

		var successFn = (function(data) {
			try {
				var page = this.createPageFragment(data);
			} catch (e) {
				if (routeData.tabPlaceholderId) {
					DeskPRO_Window.TabBar.removeTabById(routeData.tabPlaceholderId);
				}
				this._showAjaxError('<div class="error-details">There was a problem loading the tab. Here is the raw page output: <textarea class="raw">' + Orb.escapeHtml(data) + '</textarea></div>');
				return;
			}

			page.setMetaData('routeUrl', url);
			if (routeData) {
				page.setMetaData('routeData', routeData);
				if (routeData.tabPlaceholderId) {
					page.setMetaData('tabPlaceholderId', routeData.tabPlaceholderId);
				}
			}
			if (routeData.fragment) {
				page.setMetaData('fragment', routeData.fragment);
			}

			this.addPageTab(page);

			if (routeData.openCallback) {
				routeData.openCallback(page);
			}

			if (callback) callback(page);
		}).bind(this);

		var errorFn = null;

		if (routeData.preloadId) {
			preloadEl = document.getElementById(routeData.preloadId);
			if (preloadEl) {
				var content = preloadEl.innerHTML;
				preloadEl.parentNode.removeChild(preloadEl);
				content = content.replace(/<deskpro_script/g, '<script');
				content = content.replace(/<\/deskpro_script/g, '</script');
				successFn(content);
				return;
			}
		}

		this._doAjaxLoadRoute(url, routeData, successFn, errorFn);
	},



	_doAjaxLoadRoute: function(url, routeData, successFn, errorFn) {

		routeData = routeData || {};
		if (!url) {
			DP.console.warn('No URL provided! routeData: %o', routeData);
			return;
		}

		if (routeData && routeData.postData) {
			var ajaxOptions = {
				dataType: 'text',
				url: url,
				type: 'POST',
				data: routeData.postData,
				success: (function(data) {
					successFn(data);
				}).bind(this),
				noErrorOverride: true,
				timeout: 90000
			};

			if (errorFn) {
				ajaxOptions.error = function(jqXHR, textStatus, errorThrown) {
					errorFn(url, routeData, jqXHR, textStatus, errorThrown);
				};
			}

			var xhr = $.ajax(ajaxOptions);

			routeData.xhr = xhr;
		} else {
			var ajaxOptions = {
				dataType: 'text',
				url: url,
				type: 'GET',
				data: routeData.params || null,
				success: (function(data) {
					successFn(data);
				}).bind(this),
				noErrorOverride: true,
				timeout: 60000
			};

			if (routeData.ignore_perm_error) {
				ajaxOptions.ignorePermError = true;
				errorFn = function(url, routeData) {
					if (routeData.tabPlaceholderId) {
						DeskPRO_Window.TabBar.removeTabById(routeData.tabPlaceholderId);
					}
				};
			}

			if (errorFn) {
				ajaxOptions.error = function(jqXHR, textStatus, errorThrown) {
					errorFn(url, routeData, jqXHR, textStatus, errorThrown);
				};
			}

			var xhr = $.ajax(ajaxOptions);

			routeData.xhr = xhr;
		}

		return xhr;
	},



	/**
	 * This creates a PageFragment.
	 *
	 * @param {String} html The HTML page
	 * @return {DeskPRO.Agent.PageFragment.Basic}
	 */
	createPageFragment: function (html, classname, force_classname) {

		pageMeta = {
			'title': false,
			'fragmentClass': classname || 'DeskPRO.Agent.PageFragment.Basic'
		};

		var regex = /<script>([\s\S]*?)<\/script>/im;
		var matches = regex.exec(html);

		if (!matches || !matches.length) {
			var regex = /<script\s*type="text\/javascript">([\s\S]*?)<\/script>/im;
			var matches = regex.exec(html);
		}

		if (matches && matches.length) {
			eval(matches[1]);

			// Cut out the pageMeta from the HTML string
			html = html.replace(matches[0], '');
		}

		if (force_classname) {
			pageMeta.fragmentClass = classname;
		}

		// Hard switch that prevents page fragments from
		// rendering a login page into the interface
		// - The login page is redirected to within the code when session expires,
		// so in the template we set this metadata to force this redirect
		if (pageMeta && pageMeta.goToLogin) {
			window.location = BASE_URL + 'agent/';

			var page = new DeskPRO.Agent.PageFragment.Basic('');
			return page;
		}

		//DP.console.debug('PageFragment class: %s', pageMeta.fragmentClass);
		var fragment_class = Orb.getNamespacedObject(pageMeta.fragmentClass);

		var page = new fragment_class(html);
		page.setMetaData(pageMeta);

		return page;
	},


	getCurrentListPage: function() {
		return this.listPage;
	},

	getCurrentTabPage: function() {
		var tab = DeskPRO_Window.TabBar.getActiveTab();
		if (!tab) return null;

		return tab.page;
	},


	/**
	 * Reloads the currently selected tab if it has the proper rotueData metadata.
	 * This is usually only used for dev, reloading a tab rather than the full page,
	 * or re-clicking a link.
	 */
	reloadSelectedTab: function() {
		var tab = DeskPRO_Window.TabBar.getActiveTab();
		if (!tab) return;

		var route = tab.page.meta.routeData.route;

		// Delete current page so its not just deteceted as already loaded
		DeskPRO_Window.TabBar.removeTabById(tab.id);

		this.runPageRoute(route);
	},


	/**
	 * Reloads the currently selected list if it has the proper rotueData metadata.
	 */
	reloadSelectedList: function() {
		if (!this.listPage) {
			return;
		}

		var route = this.listPage.meta.routeData.route;
		this.runPageRoute(route);
	},


	/**
	 * Get the message channeler
	 */
	getMessageChanneler: function() {
		return this.messageChanneler;
	},


	/**
	 * Plays a sound through HTML5 audio element.
	 *
	 * @param files A file or array of file sources (MP3 and OGG for best cross-browser)
	 * @param options
	 * @return jQuery
	 */
	playSound: function(files, setOptions) {

		setOptions = setOptions || {};

		options = $.extend({}, {
			'autoplay': true,
			'volume': false,
			'loop': false,
			'destroyAfter': true
		}, setOptions);

		if (this.volume == 0) {
			return null;
		}

		if (typeof files == 'string') {
			files = [files];
		}

		var volume = this.volume;
		if (options.volume) {
			volume = options.volume;
		}

		volume = volume + 0.0;

		var html = [];
		html.push('<audio ');
		if (volume != 1) {
			html.push(' volume="' + volume + '" ');
		}
		if (options.loop) {
			html.push(' loop="loop" ');
		}
		html.push('>');

		Array.each(files, function(f) {
			html.push('<source src="' + f.path + '" type="' + f.type + '" />');
		});

		html.push('</audio>');
		html = html.join('');

		var el = $(html);

		try {
			el.get(0).volume = volume;
		} catch (e) {}

		if (options.destroyAfter) {
			el.bind('ended', function() {
				$(this).remove();
			});
		}

		if (options.appendTo) {
			$(options.appendTo).append(el);
		} else {
			el.appendTo('body');
		}

		if (options.autoplay) {
			try	{
				el.get(0).play();
			} catch(e) { }
		}

		return el;
	},


	/**
	 * Plays a standard sound from the static dir. This assumes an MP3
	 * and OGG version of the file exists.
	 *
	 * @param name
	 * @param options
	 */
	playLibrarySound: function(name, options) {
		if ($.browser.msie) {
			var files = [{path: ASSETS_BASE_URL + '/sounds/' + name + '.wav', type: 'audio/wav'}];
		} else {
			var files = [
				{path: ASSETS_BASE_URL + '/sounds/' + name + '.mp3', type: 'audio/mpeg'},
				{path: ASSETS_BASE_URL + '/sounds/' + name + '.ogg', type: 'audio/ogg'}
			];
		}

		this.playSound(files, options);
	},

	handleSoundElements: function(el) {
		var self = this;
		if ($(el).is('[data-play-sound]')) {
			self.playLibrarySound($(el).data('play-sound'), {appendTo: el, loop: false});
		} else {
			$('[data-play-sound]', el).each(function() {
				self.playLibrarySound($(this).data('play-sound'), {appendTo: el, loop: false});
			});
		}
	},

	//#################################################################
	//# AJAX and loading
	//#################################################################

	_globalHandleAjaxComplete: function(event, xhr, ajaxOptions) {

		var is_success = false;
		if (xhr.status && xhr.status == 200) {
			is_success = true;
		} else if (xhr.status == 0 || (xhr.statusText && xhr.statusText == 'abort')) {
			return;
		}

		// Only polling-type requests really dictate the "network" status
		if (is_success) {
			$('#network_status_indicator > a').removeClass('on').data('error-count', 0);
			$('#network_status_tip').removeClass('error');

			// If we're showing the update_running notice,
			// then the first success afterwards means
			// the helpdesk is back and we should relaod the page
			if (this.update_running) {
				window.location.reload(true);
			}
		} else {
			this.incNetworkError();
		}
	},

	showUpdateRunning: function() {
		this.update_running = true;
		$('#reload_overlay').show();
		$('#reload_overlay_updates').show();
	},

	_globalHandleAjaxError: function(event, xhr, ajaxOptions, errorThrown, force) {

		if (this.update_running) {
			return;
		}

		// status of 0 means aborted
		// eg. the user hit escape
		if (!xhr || (xhr.status == 0 && xhr.statusText != 'timeout')) {
			// ignore it, not actually an error
			return;
		}

		if (xhr && xhr.status && xhr.status == '404') {
			this.showAlert($('<div><strong>Not Found</strong><br />The page you are trying to view could not be found. It may have been moved or deleted.</div>'));
			return;
		}

		// We dont care about aborts
		// This is caused when the user navigates away from a page, any running
		// ajax requests are aborted by the browser. Without this the user
		// would see the error popup briefly before the page went away
		if (force || (xhr.statusText && xhr.statusText == 'abort')) {
			return;
		}

		if (ajaxOptions.errorDp) {
			ajaxOptions.errorDp.call(ajaxOptions.context || xhr, event, xhr, ajaxOptions, errorThrown);
		}

		var data = xhr.responseText;
		try {
			data = $.parseJSON(data);
		} catch (e) {
			data = null;
		}

		if (xhr && xhr.status == '423') {
			this.showUpdateRunning();
			return;
		}

		if (xhr && xhr.status && xhr.status == '503') {
			return window.location.reload();
		}

		if (DPC_IS_CLOUD) {
			if (xhr && xhr.status && xhr.status == '500') {
				this.showAlert($('<div>We detected a problem while trying to load the page you requested. Please try again.</div>'));
				if (DpErrorLog) {
					DpErrorLog.logError('AJAX Error ' + xhr.status + ' on ' + ajaxOptions.url);
				}
				return;
			}
			if (xhr && (xhr.status == 'timeout' || xhr.statusText == 'timeout' || xhr.responseText == 'timeout' || errorThrown == 'timeoutec')) {
				return;
			}
		}

		if (xhr && xhr.status && xhr.status == '403') {

			if (ajaxOptions.ignorePermError) {
				return;
			}

			if (data && data.error && (data.error == 'session_expired' || data.error == 'invalid_request_token')) {
				var url = data.redirect_login;
				url += '?return=' + encodeURIComponent(window.location.href);
				url += '&timeout=1';

				window.location = url;
				ajaxOptions.error = null;
				ajaxOptions.complete = null;

				$('#reload_overlay').show();

				return;
			}

			if (data && data.error && data.error == 'not_allowed') {
				var message = data.errorMessage || data.message;
				this.showAlert($('<div>The action you attempted to execute is not allowed:<br />' + message + '</div>'));
				return;
			} else {
				// All 403's should be json responses that are caught above,
				// but this is to catch other edge cases (e.g., an agent was just made a non-agent)
				if (xhr.responseText && xhr.responseText.indexOf('DeskPRO')) {
					this.showAlert($('<div><strong>No Permission</strong><br />You do not have permission to view the requested page. If you think this is a mistake, you should contact your administrator.</div>'));

				// This would mean the actual server responded with a 403--DeskPRO was not involved
				} else {
					// On cloud, a 403 generally means CF is blocking the request because it thinks we are a bot.
					if (DPC_IS_CLOUD) {
						if (DpErrorLog) {
							DpErrorLog.hasSentReport = true; // dont ask to report, just send it
							DpErrorLog.logError(
								"CloudFlare Network Error: " + message,
								'URL: ' + ajaxOptions.url,
								'agent',
								1,
								true
							);
						}
						// Try reloading the interface
						// In case of CF blocks, this would result in the user seeing a "challenge" response
						// which will let them whitelist themselves
						this.util.reloadInterface();
					} else {
						this.showAlert($('<div><strong>Server Error</strong><br />You do not have permission to view the requested page. If you think this is a mistake, you should contact your administrator.</div>'));
					}
				}

				return;
			}
		}

		// We dont use this handler if there was an error handler used
		if (ajaxOptions && ajaxOptions.error && !ajaxOptions.noErrorOverride) return;

		// We dont show the error popup if it was just an error with polling
		if (ajaxOptions && ajaxOptions.dpIsPolling) {
			this.incNetworkError();
			return;
		};

		if (xhr.status == 'timeout' || xhr.statusText == 'timeout' || xhr.responseText == 'timeout' || errorThrown == 'timeout') {
			this.incNetworkError();
			return;
		}

		// We dont know if the request was JSON or HTML (eg the sn code might be embedded in html in json),
		// so we have to sniff the raw responseText to see about any embedded SN code
		var sn = null;
		if (data && data.sn) {
			sn = data.sn;
		} else {
			var match = /\[SN([0-9A-Z]{8})\]/.exec(xhr.responseText);
			if (match) {
				sn = match[1];
			}
		}

		// Show overlay about failed
		if (sn) {
			var status = (xhr.status || '') + ' ' + (errorThrown || '') + ' ' + (xhr.statusText || '');
			var url    = ajaxOptions.url;
			var method = ajaxOptions.type;

			var showsn = 'SN' + sn;
			if (DESKPRO_PERSON_ISADMIN) {
				showsn = '<a href="' + BASE_URL + 'admin/server/error-logs/SN' + sn + '">SN' + sn + '</a>';
			}

			this._showAjaxError('<div>If the error persists, give your administrator this code: ' + showsn + '</div><div class="error-details">Here is the raw output returned from the server error:<textarea class="raw">' + Orb.escapeHtml(method) + ' ' + Orb.escapeHtml(url) + "\n" + Orb.escapeHtml(status) + "\n\n" + Orb.escapeHtml(xhr.responseText) + '</textarea></div>');
		} else {
			this._showAjaxError('<div class="error-details">Here is the raw output returned from the server error:<textarea class="raw">' + Orb.escapeHtml(method) + ' ' + Orb.escapeHtml(url) + "\n" + Orb.escapeHtml(status) + "\n\n" + Orb.escapeHtml(xhr.responseText) + '</textarea></div>');
		}
	},

	incNetworkError: function() {
		var a = $('#network_status_indicator > a').addClass('on');
		a.data('error-count', parseInt(a.data('error-count')) + 1);
		$('#network_status_tip').addClass('error');
	},

	_showAjaxError: function(message, type) {

		if (DPC_IS_CLOUD) {
			if (message.indexOf('http://www.cloudflare.com/') !== -1 && message.indexOf('<title>Website is currently unreachable</title>') !== -1) {
				if (DpErrorLog) {
					DpErrorLog.hasSentReport = true; // dont ask to report
					DpErrorLog.logError(
						"CloudFlare Network Error: " + message,
						'',
						'agent',
						1
					);
				}
				return;
			}
		}

		$('#global_ajax_error_info').empty();
		if (message) {
			$('#global_ajax_error_info').html(message);
		} else {
			$('#global_ajax_error_info').empty();
		}

		if (!this.ajaxErrorOverlay) {
			this.ajaxErrorOverlay = new DeskPRO.UI.Overlay({
				contentElement: $('#global_ajax_error'),
				zIndex: 50000 /* this should be bigger than everything */
			});
		}

		this.ajaxErrorOverlay.initOverlay(); // needed so we can access wrapperOuter next
		this.ajaxErrorOverlay.elements.wrapperOuter.addClass('error');
		$('#global_ajax_error').removeClass('switch-success switch-loading');

		this.ajaxErrorOverlay.openOverlay();
	},



	//#################################################################
	//# Inits
	//#################################################################

	_initBasic: function() {

		this.options.messageChanneler.interval = DP_POLLER_INTERVAL;
		this.messageChanneler = new DeskPRO.MessageChanneler.AjaxChanneler(this.messageBroker, this.options.messageChanneler);
		//this.messageChanneler = new DeskPRO.MessageChanneler.AbstractChanneler(this.messageBroker, this.options.messageChanneler);

		this.messageChanneler.poller.addData((function () {
			return {'at': parseInt(this.activityTime.getTime() / 1000)};
		}).bind(this), 'at', { recurring: true });

		// Add chats we're looking at right now
		this.messageChanneler.poller.addData((function () {
			var chatIdsData = [];
			Array.each(this.getTabWatcher().findTabType('userchat'), function(t) {
				chatIdsData.push({
					name: 'chat_ids[]',
					value: t.page.meta.conversation_id
				});
			});

			if (!chatIdsData.length) {
				return false;
			}

			return chatIdsData;
		}).bind(this), 'chat_ids', { recurring: true });

		this.getTabWatcher().addTabTypeWatcher('userchat', new DeskPRO.Agent.WindowElement.TabWatcher.UserChat());
	},

	_initRoutes: function() {
		// Set ourselves up as the first route listener
		var cb = function(routeData) {
			this.loadRoute(routeData);
		};
		this.addPageRouteLoader('listpane', cb.bind(this));
		this.addPageRouteLoader('page', cb.bind(this));
		this.addPageRouteLoader('article', cb.bind(this));
		this.addPageRouteLoader('download', cb.bind(this));
		this.addPageRouteLoader('news', cb.bind(this));
		this.addPageRouteLoader('guides', cb.bind(this));
		this.addPageRouteLoader('feedback', cb.bind(this));
		this.addPageRouteLoader('org', cb.bind(this));

    var loaded = {};
		this.addPageRouteLoader('ticket', (function(routeData) {

			routeData.forTypename = 'ticket';

			var m = routeData.url.match(/tickets\/([0-9]+)/);
			if (!m || !m[1]) {
				console.warn('Bad page loader call: ' + routeData.url + ' %o', routeData);
				return;
			}
			var ticketId = m[1];

      if (loaded[ticketId]) return;

			routeData.tabLoad = function() {
        loaded[ticketId] = setTimeout(function(){ delete loaded[ticketId] }, 400);
				DeskPRO_Window.getMessageBroker().sendMessage('ui.ticket.opened', { ticketId: ticketId });
			};
			routeData.tabUnload = function() {
				DeskPRO_Window.getMessageBroker().sendMessage('ui.ticket.closed', { ticketId: ticketId });
			};
			this.loadRoute(routeData);
		}).bind(this));
		this.addPageRouteLoader('person', this.loadRoute.bind(this));
		this.addPageRouteLoader('kb_article_view', this.loadRoute.bind(this));
		this.addPageRouteLoader('kb_article_new', this.loadRoute.bind(this));
		this.addPageRouteLoader('kb_article_edit', this.loadRoute.bind(this));
		this.addPageRouteLoader('voice', this.loadRoute.bind(this));
		this.addPageRouteLoader('poppage', this.loadRouteOverlay.bind(this));
	},

	_initWindowInterface: function() {
		var self = this;

		this.notifications = new DeskPRO.Agent.Notifications();

		// Global AJAX handler for errors if no error handler is attached
		$(document).ajaxError(this._globalHandleAjaxError.bind(this));
		$(document).ajaxComplete(this._globalHandleAjaxComplete.bind(this));

		// The favicon count
		this.faviconBadge = new DeskPRO.FaviconBadge({
			favicon: '#favicon'
		});

		this.notifications.addEvent('modCount', function(data) {
			var count = data.count || 0;

			var doanim = false;
			if (!$('html').is('.window-active')) {
				doanim = true;
			}

			self.faviconBadge.updateBadge(count, true);
		});

		var autostart = false;

		if (DESKPRO_PERSON_PERMS['agent_tickets.create']) {
			var self = this;
			this.newTicketLoader = new DeskPRO.Agent.Widget.BackgroundPopout({
				loadUrl: BASE_URL + 'agent/tickets/new',
				tabRoute: 'page:' + BASE_URL + 'agent/tickets/new',
				autostart: autostart
			});
			this.newTicketLoader.newLinkedTicket = function(ticket_id, message_id) {
				self.newTicketLoader.nextParams = {
					ticket_id: ticket_id,
					message_id: message_id || 0
				};
				self.newTicketLoader.open();
			};
			$('#create_ticket_btn').on('click', function() { DeskPRO_Window.newTicketLoader.toggle(); });
		}

		if (DESKPRO_PERSON_PERMS['agent_people.create']) {
			this.newPersonLoader = new DeskPRO.Agent.Widget.BackgroundPopout({
				loadUrl: BASE_URL + 'agent/people/new',
				tabRoute: 'page:' + BASE_URL + 'agent/people/new',
				autostart: autostart
			});
		}

		if (DESKPRO_PERSON_PERMS['agent_org.create']) {
			this.newOrganizationLoader = new DeskPRO.Agent.Widget.BackgroundPopout({
				loadUrl: BASE_URL + 'agent/organizations/new',
				tabRoute: 'page:' + BASE_URL + 'agent/organizations/new',
				autostart: autostart
			});
		}

		if (DESKPRO_PERSON_PERMS['agent_publish.create']) {
			this.newArticleLoader = new DeskPRO.Agent.Widget.BackgroundPopout({
				loadUrl: BASE_URL + 'agent/kb/article/new',
				tabRoute: 'page:' + BASE_URL + 'agent/kb/article/new',
				autostart: autostart
			});
			this.newNewsLoader = new DeskPRO.Agent.Widget.BackgroundPopout({
				loadUrl: BASE_URL + 'agent/news/new',
				tabRoute: 'page:' + BASE_URL + 'agent/news/new',
				autostart: autostart
			});
			this.newDownloadLoader = new DeskPRO.Agent.Widget.BackgroundPopout({
				loadUrl: BASE_URL + 'agent/downloads/new',
				tabRoute: 'page:' + BASE_URL + 'agent/downloads/new',
				autostart: autostart
			});
			this.newFeedbackLoader = new DeskPRO.Agent.Widget.BackgroundPopout({
				loadUrl: BASE_URL + 'agent/feedback/new',
				tabRoute: 'page:' + BASE_URL + 'agent/feedback/new',
				autostart: autostart
			});
      this.newTopicLoader = new DeskPRO.Agent.Widget.BackgroundPopout({
        loadUrl: BASE_URL + 'agent/guides/new',
        tabRoute: 'page:' + BASE_URL + 'agent/guides/new',
        autostart: autostart
      });
		}

		this.newTaskLoader = new DeskPRO.Agent.Widget.BackgroundPopout({
			loadUrl: BASE_URL + 'agent/tasks/new',
			tabRoute: 'page:' + BASE_URL + 'agent/tasks/new',
			autostart: autostart
		});

		var getkbbackdrop = function() {
			if (this.el) {
				return this.el;
			}

			this.el = $('<div />').addClass('backdrop').appendTo('body').on('click', function() {
				$('#dp_keyboard_shortcuts').hide();
				getkbbackdrop().hide();
			});
			return this.el;
		};

		$('#dp_keyboard_shortcuts').find('.close').on('click', function() {
			$('#dp_keyboard_shortcuts').hide();
			getkbbackdrop().hide();
		});
		$('#keyboard_shortcuts_trigger').on('click', function() {
			$('#dp_keyboard_shortcuts').show();
			getkbbackdrop().show();
		});

		DeskPRO_Window.getMessageBroker().addMessageListener('agent.ui.reload', function (info) {
			DeskPRO_Window.showRefreshAlert(info.person_name);
		});

		this.keyboardShortcuts = new DeskPRO.Agent.KeyboardShortcuts();
	},

	initStickyTips: function(els) {
		if (!els.hasClass('with-stickytip')) {
			els = els.find('.with-stickytip');
		}

		els.each(function() {
			if ($(this).hasClass('dp-stickytip-init')) {
				return;
			}

			var me = $(this);
			$(this).addClass('dp-stickytip-init');

			$(this).one('mouseover', function() {
				$(this).attr('title', '');
				var target = $(me.data('stickytip-target'));
				me.data('stickytip-target', target);

				var hideTimout = null;
				var hideFn = function() {
					if (target.hasClass('over') || me.hasClass('over')) {
						return;
					}

					target.hide();
					target.removeClass('over');
					me.removeClass('over');
				};

				var showFn = function() {
					var pos = me.offset();
					var left = pos.left;
					var winW = $(window).width();
					var w = target.width();
					if (left + w + 15 > winW) {
						left = winW-w-30;
					}
					target.css({
						left: left,
						top: pos.top + 20
					});
					target.show();
				};

				var hideTimeout = null;

				target.on('mouseover', function() {
					target.addClass('over');
					if (hideTimeout) {
						window.clearTimeout(hideTimeout);
						hideTimeout = null;
					}
				}).on('mouseout', function() {
					target.removeClass('over');
					if (hideTimeout) {
						window.clearTimeout(hideTimeout);
						hideTimeout = null;
					}
					hideTimeout = window.setTimeout(hideFn, 240);
				});

				me.on('mouseover', function() {
					me.addClass('over');
					showFn();
					if (hideTimeout) {
						window.clearTimeout(hideTimeout);
						hideTimeout = null;
					}
				}).on('mouseout', function() {
					me.removeClass('over');
					if (hideTimeout) {
						window.clearTimeout(hideTimeout);
						hideTimeout = null;
					}
					hideTimeout = window.setTimeout(hideFn, 240);
				});

				me.addClass('over');
				target.detach().appendTo('body');
				showFn();
			});
		});
	},

	_initSections: function() {

		var self = this;
		var count = -1;

		var secttimeout = 2500;

		this.getSectionDataStartQueue();

		$('#dp_nav [data-section-handler], #agent_chat_section').each(function() {
			var el = $(this);
			if (!el.attr('id')) {
				el.attr('id', Orb.getUniqueId('section_'));
			}

			var handlerClassName = el.data('section-handler');

			if (DeskPRO_Window.DEBUG.disableSectionHandlers) {
				if (!DeskPRO_Window.DEBUG.enableSectionHandlers || DeskPRO_Window.DEBUG.enableSectionHandlers.indexOf(handlerClassName) === -1) {
					return;
				}
			}

			var handlerClass = Orb.getNamespacedObject(handlerClassName);
			var handler = new handlerClass();
			handler.section_id = el.attr('id');

			if (++count) {
				handler.addEvent('sectionInit', function() {
					window.setTimeout(function() { handler._loadAutoLoadRoutes(true); }, secttimeout);
					secttimeout += (400 * count);
				});
			} else {
				// First one, load it for real
				handler.addEvent('sectionInit', function() {
					handler._loadAutoLoadRoutes();
				});
			}

			self.sections[el.attr('id')] = handler;

			if (!el.is('.no-click-switch')) {
				el.on('click', function() { self.switchToSection(el.attr('id')) });
			}
		});

		this.getSectionDataSendQueued();
	},

	switchToSection: function(section_id, no_load_list) {

		DP.console.debug('Switching to %s', section_id);

		var handler = this.sections[section_id];
		if (!handler) {
			if (section_id != 'test_section') {
				DP.console.warn('Invalid section: %s', section_id);
			}
			return;
		}

		// Already active
		if (this.openSection === handler) {
			window.document.dispatchEvent(new CustomEvent('dpChangeSection'));
			return;
		}

		var btn = $('#' + section_id);

		// Already on
		if (btn.is('.on')) {
			return;
		}

		if (this.openSection) {
			this.openSection.fireEvent('hide');
		}

		$('#dp_nav li.active').removeClass('active');
		btn.addClass('active');

		$('#dp_source > section.on').removeClass('on');
		$('#dp_list > section.on').removeClass('on');


		$('#dp_list_loading, #dp_source_loading').addClass('on');

		if (this.openSection) {
			this.openSection.fireEvent('afterhide');
		}

		$('#dp_list_loading').removeClass('on');

		handler.fireEvent('show', [no_load_list]);
		var sectionEl = handler.getSectionElement();
		if (sectionEl) {
			sectionEl.addClass('on');
		}
		var listEl = handler.getListElement();
		if (listEl) {
			listEl.addClass('on');
		}
		handler.fireEvent('aftershow', [no_load_list]);

		this.openSection = handler;
		window.document.dispatchEvent(new CustomEvent('dpChangeSection'));

		if (this.openSection.listPage) {
			this.listPage = this.openSection.listPage;
		}

		if (!this.paneVis.source) {
			this.layout.openSourceOverlay();
		}

		this.updateWindowUrlFragment();
		if (this.openSection) {
			this.openSection.updateUi();
			if (this.listPage) {
				this.listPage.updateUi();
			}
		}
	},

	getOpenSection: function() {
		return this.openSection;
	},

	_initLayout: function() {

		this.layout = new DeskPRO.Agent.Layout.DeskproWindow();
		this.layout.doResize(true);

		this.TabBar = new DeskPRO.Agent.WindowElement.TabBar({
			tabPane: $('#tabNavigationPane'),
			bodyPane: $('#dp_content_wrap'),
			menuBtn: $('#tabDropdownPicker')
		});

		this.tabWatcher = new DeskPRO.Agent.TabWatcher({
			tabManager: DeskPRO_Window.TabBar
		});

		this.tabWatcher.addTabTypeWatcher('ticket', new DeskPRO.Agent.WindowElement.TabWatcher.Tickets());

		this.recentTabs = new DeskPRO.Agent.RecentTabs();
	},

	_initInterfaceServices: function() {
		this.popover_inited = {};
		this.initInterfaceLayerEvents(document);
	},

	_initInterfacePopover: function(el, opennow) {
		var popover_inited = this.popover_inited;

		var route = el.data('route');
		var routeData = this.parseRoute(route);

		if (el.data('route-preload-id')) {
			routeData.preloadId = el.data('route-preload-id');
		}

		var popover;

		if (!popover_inited[route]) {

			popover = new DeskPRO.Agent.PageHelper.Popover({
				pageUrl: routeData.url,
				preloadId: routeData.preloadId,
				tabRoute: route,
				loadTimeout: (el.is('.preload') ? 1500 : 0)
			});

			popover_inited[route] = {
				count: 0,
				popover: popover
			};

			popover.addEvent('close', function() {
				if (popover_inited[route].count < 1) {
					popover_inited[route].popover.destroy();
					delete popover_inited[route];
				}
			});

			popover.addEvent('destroy', function() {
				delete popover_inited[route];
			});
		} else {
			popover = popover_inited[route].popover;
		}

		popover_inited[route].count++;

		var tabWrapper = el.closest('.with-page-fragment');
		if (tabWrapper.length) {
			var page = tabWrapper.data('page-fragment');
			page.addEvent('destroy', function() {
				if (popover_inited[route]) {
					popover_inited[route].count--;
					if (popover_inited[route].count < 1) {
						popover_inited[route].popover.destroy();
						delete popover_inited[route];
					}
				}
			});
		} else {
			popover.options.destroyOnClose = true;
		}

		return popover;
	},

	/**
	 * Attaches central handlers on a layer. These handlers are added to the document,
	 * but if you have a new layer that prevents propagation up to the document,
	 * then you'll need to init it as a new layer with its own handlers.
	 *
	 * @param context
	 */
	initInterfaceLayerEvents: function(context) {
		if ($(context).is('.dp-interface-layer')) {
			return;
		}

		$(context).addClass('dp-interface-layer');

		this.initRoutes(context);
		this.initQtip(context);

		$('.timeago', context).timeago();
		DeskPRO.ElementHandler_Exec(context);
	},

	initRoutes: function(context) {
		var self = this;

    function cancelRouteSelection(ev) {
      ev.preventDefault();
      ev.stopPropagation();

      // This is because shift-clicking a non-link can result
      // in text selection
      if (ev.shiftKey) {
        if (document.getSelection) {
          document.getSelection().removeAllRanges();
        }
      }
    }

    window.setTimeout(function() {
      // Accept clicks on routes
      $(context).on('mousedown', '[data-route]', function(ev) {
        cancelRouteSelection(ev);
      });
      $(context).on('click', '[data-route]', function(ev) {
        if ($(this).is('.as-popover') || $(this).is('.cancel-route')) {
          return;
        }

        if ($(this).is('.row-item') && (!$(ev.target).is('.click-through') && $(ev.target).is('input, a, button, textarea'))) {
          return;
        }

        cancelRouteSelection(ev);

        self.runPageRouteFromElement($(this), { event: ev });

        if (window.DP_FRAME_OVERLAYS) {
          Object.keys(window.DP_FRAME_OVERLAYS).forEach(function(key) {
            var iframe = window.DP_FRAME_OVERLAYS[key];
            if (iframe.opened) {
              iframe.close();
            }
          });
        }

        // If this was a list-pane and we have an open popover,
        // we need to close the popover so the listpane can actually load
        if ($(this).data('route').indexOf('listpane:') === 0) {
          Object.each(DeskPRO.Agent.PageHelper.Popover_Instances, function(inst) {
            if (inst.isOpen()) {
              inst.close();
            }
          }, this);
        }
      });

      $(context).on('click', '.agent-link', function(ev) {
        ev.preventDefault();
        ev.stopPropagation();

        var agentId = $(this).data('agent-id');
        DP.console.log('Agent click %i', agentId);
        if (!agentId || agentId === '0' || agentId === '' || agentId == DESKPRO_PERSON_ID) {
          return;
        }

        if (!DeskPRO_Window.sections.agent_chat_section) {
          DP.console.warn('The agent chat section is not enabled');
          return;
        }

        DeskPRO_Window.sections.agent_chat_section.newChatWindow([agentId]);
      });

      $(context).on('click', '.as-popover', function(ev) {
        ev.preventDefault();
        ev.stopPropagation();
        self._initInterfacePopover($(this)).toggle();
      });
    }, 100);
	},

	initQtip: function(context) {
    window.setTimeout(function() {
      $(context).find('.tipped').one('mouseover', function(ev) {

        if ($(this).hasClass('tipped-inited')) {
          return;
        }
        $(this).addClass('tipped-inited');

				var options = {};
				if ($(this).data('tipped-options')) {
					eval('options = {' + $(this).data('tipped-options') + '}');
				}

        var qtipOptions = {};

        if (options.ajax) {
          qtipOptions.content = {
            text: 'Loading...',
            ajax: {
              url: $(this).data('tipped'),
              type: 'GET'
            }
          };
        } else if ($(this).data('tipped')) {
          qtipOptions.content = {
            attr: 'data-tipped'
          };
        } else {
          qtipOptions.content = {
            attr: 'title'
          };
        }

        if (options.inline) {
          qtipOptions.content.attr = null;
          var el = $('#' + $(this).data('tipped'));
          qtipOptions.content.text = function() {
            return Orb.escapeHtml(el.text());
          };
        }

        if (qtipOptions.content.attr && !$(this).data('as-html')) {
          var me = $(this);
          var attr = qtipOptions.content.attr;
          qtipOptions.content.text = function() {
            return Orb.escapeHtml(me.attr(attr) || '');
          };
          qtipOptions.content.attr = null;
        }

        qtipOptions.style = {
          classes: 'ui-tooltip-shadow ui-tooltip-rounded'
        };

        qtipOptions.position = {
          my: 'top center',
          at: 'bottom center',
          viewport: $(window)
        };

        qtipOptions = $.extend(true, qtipOptions, options);

        $(this).qtip(qtipOptions).qtip('show', ev);
        $(this).addClass('tipped-inited');
      });
    }, 200);
	},

	initInterfaceServices: function(context) {
		var self = this;
		var page = false;

		if (context.hasClass('dp-inited-iface')) {
			return;
		}

		if (context.is('.with-page-fragment')) {
			page = context.data('page-fragment');
		} else {
			var tabWrapper = context.closest('.with-page-fragment');
			page = tabWrapper.data('page-fragment');
		}

		$('.as-popover.preload', context).each(function() {
			self._initInterfacePopover($(this));
		});

		if (page) {
      $('.with-scrollbar', context).each(function() {
				new DeskPRO.Agent.ScrollerHandler(page, $(this), {
					showEvent: 'show',
					hideEvent: 'hide'
				});
			});
		}

		$('.timeago', context).timeago();
		DeskPRO.ElementHandler_Exec(context);

		$('input.dp-checkbox', context).each(function() {
			DeskPRO_Window.util.dpCheckbox($(this));
		});

		this.initQtip(context);
	},

	getSectionData: function(section_id, callback, extra_data) {
		var self = this;
		var url;

		if (!this.loadingSections) {
			this.loadingSections = {};
		}

		if (this.loadingSections[section_id]) {
			return;
		}

		this.loadingSections[section_id] = true;

		// If we're in queued mode, then dont send anything yet
		if (this._getSectionDataQueued) {
			this._getSectionDataQueued.push([section_id, callback]);
			return;
		}

		switch (section_id) {
			case 'tickets_section':
				url = BASE_URL + 'agent/ticket-search/get-section-data.json';
				break;

			case 'chat_section':
				url = BASE_URL + 'agent/chat/get-section-data.json';
				break;

			case 'people_section':
				url = BASE_URL + 'agent/people/get-section-data.json';
				break;

			case 'feedback_section':
				url = BASE_URL + 'agent/feedback/get-section-data.json';
				break;

			case 'publish_section':
				url = BASE_URL + 'agent/publish/get-section-data.json';
				break;

			case 'tasks_section':
				url = BASE_URL + 'agent/tasks/get-section-data.json';
				break;

			case 'agent_chat_section':
				url = BASE_URL + 'agent/agent-chat/get-section-data.json';
				break;
		}

		if (!url) {
			DP.console.warn('getSectionData: Unknown section %s', section_id);
			return;
		}

		var errorFn = function() {
			this.tryCount++;
			if (this.tryCount <= this.retryLimit) {
				$.ajax(this);
				return;
			}
			delete self.loadingSections[section_id];
		};

		$.ajax({
			url: url,
			data: extra_data || {},
			timeout: 90000,
			dataType: 'json',
			success: function(data) {
				delete self.loadingSections[section_id];

				if (!data || !data.section_html) {
					errorFn();
					return;
				}

				callback(data);
			},
			tryCount : 0,
		    retryLimit: 3,
			error: function(xhr, textStatus, errorThrown) {
				errorFn();
				DeskPRO_Window._globalHandleAjaxError(null, xhr, this, errorThrown);
			}
		});
	},

	getSectionDataStartQueue: function() {
		this._getSectionDataQueued = [];
	},

	getSectionDataSendQueued: function() {
		var self = this;
		if (!this._getSectionDataQueued || !this._getSectionDataQueued.length) {
			return;
		}

		var sectionDataQueued = this._getSectionDataQueued;
    this._getSectionDataQueued = null;

		var callback_map = {};
		var data = [];
		Array.each(sectionDataQueued, function(info) {
      if (info[0] === 'tickets_section') {
        // tickets done itself
        self.loadingSections['tickets_section'] = false;
        self.getSectionData('tickets_section', function(d) { info[1](d); });
      } else {
        data.push({
          name: 'section_ids[]',
          value: info[0]
        });

        callback_map[info[0]] = info[1];
      }
		});

    if (!data.length) {
      return;
    }

		$.ajax({
			url: BASE_URL + 'agent/get-combined-section-data.json',
			type: 'GET',
			data: data,
			dataType: 'json',
			timeout: 90000,
			tryCount : 0,
		    retryLimit: 3,
			error: function(xhr, textStatus, errorThrown) {
				this.tryCount++;
				if (this.tryCount <= this.retryLimit) {
					$.ajax(this);
					return;
				}
				var status = (xhr.status || '') + ' ' + (errorThrown || '') + ' ' + (xhr.statusText || '');
				self._showAjaxError('<div class="error-details">Here is the raw output returned from the server error:<textarea class="raw">' + status + "\n\n" + Orb.escapeHtml(xhr.responseText) + '</textarea></div>');

				self.loadingSections = {};
			},
			success: function(data) {
				Object.each(data, function(sectionData, sectionId) {
          self.loadingSections[sectionId] = false;
					if (sectionData === null || !sectionData.section_html) {
						// Means an error, send it normally
						self.getSectionData(sectionId);
					}
					if (callback_map[sectionId]) {
						callback_map[sectionId](sectionData);
					}
				});
			}
		});
	},

	prepareWidgetedHtml: function(html) {
		var finalHtml = html,
			widgetCssRegex = /<style type="text\/css" data-widget="(\d+)" data-hash="([a-zA-Z0-9]+)">([\s\S]*?)<\/style>/g,
			widgetJsRegex = /<script type="text\/javascript"([^>]*)>([\s\S]*?)<\/script>/g,
			cssExists = {},
			jsSource = [],
			jsInline = [],
			match;

		$('style[data-widget]').each(function () { cssExists[$(this).data('widget')] = $(this).data('hash'); });

		while (match = widgetCssRegex.exec(html)) {
			finalHtml = finalHtml.replace(match[0], '');

			// only insert the CSS once
			if (cssExists[match[1]] !== match[2]) {
				cssExists[match[1]] = match[2];
				$(match[0]).appendTo('head');
			}
		}

		while (match = widgetJsRegex.exec(html)) {
			finalHtml = finalHtml.replace(match[0], '');

			if (match[1].match(/src="([^"]+)"/)) {
				jsSource.push(RegExp.$1);
			} else {
				if (match[2]) {
					jsInline.push({
						widget: match[1].match(/data-widget="(\d+)"/) ? RegExp.$1 : false,
						htmlId: match[1].match(/data-html-id="([^"]+)"/) ? RegExp.$1 : false,
						code: match[2]
					});
				}
			}
		}

		return {
			html: finalHtml,
			jsSource: jsSource,
			jsInline: jsInline
		};
	},

	runWidgetedJs: function(page, src, inline) {
		var run = function() {
			for (var i = 0; i < inline.length; i++) {
				var code = inline[i].code,
					htmlId = inline[i].htmlId,
					context;

				if (inline[i].widget) {
					context = {
						page: page,
						meta: page.getAllMetaData(),
						id: htmlId,
						containerEl: (htmlId ? $('#' + htmlId + '_container') : false),
						contentEl: (htmlId ? $('#' + htmlId) : false),
						tabEl: (htmlId ? $('#' + htmlId + '_tab') : false)
					};
					eval('(function() {' + code + '}).call(context);');
				} else {
					$.globalEval(code);
				}
			}
		};

		if (!src.length) {
			run();
		} else {
			var remaining = src.length;

			for (var i = 0; i < src.length; i++) {
				$.ajax({
					url: src[i],
					type: 'GET',
					dataType: 'script',
					cache: true
				}).always(function() {
						remaining--;
						if (remaining == 0) {
							run();
						}
					});
			}
		}
	},

	canUseAgentReplyRte: function() {
		return true;
	},

	initRteAgentReply: function(textarea, options) {
		return DeskPRO.Agent.RteEditor.initRteAgentReply(textarea, options);
	},

	initAgentNotifierForRte: function(obj, textarea, alwaysAvailable) {
		var api = textarea.data('redactor');
		if (!api) {
			return;
		}

		var ed = textarea.getEditor();
		var self = this;

		var agentMapLower = {}, hasAgents = false;
		var notifyAgentMap = window.notifyAgentMap || [];
		Object.each(notifyAgentMap, function(data, agentId) {
			hasAgents = true;
			agentMapLower[agentId] = data.name.toLowerCase();
		});

		if (!hasAgents) {
			return;
		}

		obj.agentNotifyList = $('<ul />').addClass('message-agent-notify-list').hide().appendTo(document.body);

		var insertAgentNotify = function(agentId) {
			if (typeof notifyAgentMap[agentId] === 'undefined') {
				return;
			}

			self.hideAgentNotifyList(obj);

			var focus = api.getFocus(),
				focusNode = $(focus[0]),
				testText;

			if (!focus || !focus[0]) {
				return;
			}

			if (focus[0].nodeType == 3) {
				testText = focusNode.text().substring(0, focus[1]);
			} else {
				focus[0] = focusNode.contents().get(focus[1] - 1);
				focusNode = $(focus[0]);
				testText = focusNode.text();
				focus[1] = testText.length;
			}

			var	lastAt = testText.lastIndexOf('@');

			if (lastAt != -1) {
				api.setSelection(focus[0], lastAt, focus[0], focus[1]);
			}

			// web kit handles content editable without an issue. this prevents the span
			// from being extended unnecessarily
			var editable = $.browser.webkit ? ' contenteditable="false"' : '';
			api.insertHtml('<span' + editable + ' data-notify-agent-id="' + agentId + '">@' + Orb.escapeHtml(notifyAgentMap[agentId].name) + '</span>&nbsp;');
		};

		obj.agentNotifyList.on('mousedown', 'li', function(e) {
			e.preventDefault();
			insertAgentNotify($(this).data('agent-id'));
		});

		ed.on('click blur', function() {
			if (obj.isNote || alwaysAvailable) {
				self.hideAgentNotifyList(obj);
			}
		});

		ed.on('keydown', function(e) {
			if (!obj.isNote && !alwaysAvailable) {
				self.hideAgentNotifyList(obj);
				return;
			}

			switch (e.keyCode) {
				case 38: // up
				case 40: // down
				case 13: // enter
					if (!obj.agentNotifyList.is(':visible')) {
						return;
					}
					break;

				default:
					return;
			}

			e.preventDefault();

			if (e.keyCode == 13) { // enter - inserting the selected
				var li = obj.agentNotifyList.find('li.selected');
				if (!li.length) {
					li = obj.agentNotifyList.find('li:first');
				}

				insertAgentNotify(li.data('agent-id'));
			} else if (e.keyCode == 40) { // down - moves down the list
				var li = obj.agentNotifyList.find('li.selected');
				if (!li.length) {
					obj.agentNotifyList.find('li:first').addClass('selected');
				} else {
					li.removeClass('selected');
					var next = li.next('li');
					if (next.length) {
						next.addClass('selected');
					} else {
						obj.agentNotifyList.find('li:first').addClass('selected');
					}
				}
			} else if (e.keyCode == 38) { // up - moves up the list
				var li = obj.agentNotifyList.find('li.selected');
				if (!li.length) {
					obj.agentNotifyList.find('li:last').addClass('selected');
				} else {
					li.removeClass('selected');
					var prev = li.prev('li');
					if (prev.length) {
						prev.addClass('selected');
					} else {
						obj.agentNotifyList.find('li:last').addClass('selected');
					}
				}
			}
		});

		ed.on('keyup', function(e) {
			if (!obj.isNote && !alwaysAvailable) {
				return;
			}

			if (e.ctrlKey || e.metaKey) {
				return;
			}

			switch (e.keyCode) {
				case 16: // shift
				case 17: // ctrl
				case 18: // alt
				case 19: // pause/break
				case 20: // caps lock
				case 91: // left windows
				case 92: // right windows
				case 93: // select
				case 224: // apple key
					return;

				case 13: // enter
				case 38: // up
				case 40: // down
					// these don't hide as that messes up the keydown handler
					e.stopImmediatePropagation();
					e.preventDefault();
					return;

				case 9: // tab
				case 27: // esc
				case 33: // page up
				case 34: // page down
				case 35: // end
				case 36: // home
				case 37: // left
				case 39: // right
					self.hideAgentNotifyList(obj);
					return;

				default:
					// function keys and other special ones
					if (e.keyCode >= 112 && e.keyCode <= 145) {
						self.hideAgentNotifyList(obj);
						return;
					}
			}

			var focus = api.getFocus(),
				origin = api.getOrigin(),
				selection = api.getSelection();

			if (focus[0] != origin[0] || focus[1] != origin[1]) {
				// selected multiple points, don't show
				self.hideAgentNotifyList(obj);
				return;
			}

			var	focusNode = $(focus[0]),
				testText = focus[0].nodeType == 3 ? focusNode.text().substring(0, focus[1]) : $(focusNode.contents().get(focus[1] - 1)).text(),
				lastAt = testText.lastIndexOf('@'),
				matches = [];

			if (lastAt != -1 && (lastAt == 0 || testText[lastAt - 1].match(/^(\s|[\.!?:;,()<>|/-])$/))) {
				var afterAt = testText.substring(lastAt + 1, testText.length).toLowerCase();

				if (afterAt.length >= 2 && afterAt.length < 75) {
					Object.each(notifyAgentMap, function(data, agentId) {
						if (agentMapLower[agentId].indexOf(afterAt) == 0) {
							matches.push(agentId);
						}
					});
				}
			}

			if (matches.length) {
				var selectedId = obj.agentNotifyList.find('li:selected').data('agent-id');

				obj.agentNotifyList.empty();
				for (var i = 0; i < matches.length; i++) {
					var li = $('<li>')
						.text(notifyAgentMap[matches[i]].name)
						.css('background-image', 'url('+notifyAgentMap[matches[i]].picture_url+')')
						.data('agent-id', matches[i]);
					if (matches[i] === selectedId) {
						li.addClass('selected');
					}
					obj.agentNotifyList.append(li);
				}

				if (!obj.agentNotifyList.find('li:selected').length) {
					obj.agentNotifyList.find('li:first').addClass('selected');
				}

				var containingNode = focus[0].nodeType == 3 ? focusNode.parent() : focusNode;
				if (!containingNode.is('div, p, li, ul, ol, blockquote, table, body')) {
					containingNode = containingNode.closest('div, p, li, ul, ol, blockquote, table, body');
				}
				var offset = containingNode.offset();

				if (selection) {
					var selOffset = Orb.getSelectionCoords(selection);
					if (selOffset) {
						offset = selOffset;
					}
				}

				obj.agentNotifyList.css({
					top: offset.top - obj.agentNotifyList.outerHeight() - 1,
					left: offset.left
				});

				obj.agentNotifyList.show();
				obj.agentNotifyListShown = true;
			} else {
				self.hideAgentNotifyList(obj);
			}
		});

		// this is important as I need this keyup handler to run before redactor's own because of new line handling
		ed.data('events').keyup.reverse();
	},

	hideAgentNotifyList: function(obj) {
		if (obj.agentNotifyList && obj.agentNotifyListShown) {
			obj.agentNotifyList.empty().hide();
			obj.agentNotifyListShown = false;
		}
	},

	//##################################################################################################################
	// Notices Window
	//##################################################################################################################

	setPaneVis: function(id, vis) {
		this.paneVis[id] = vis;
	},

	setPaneVisNum: function(num) {
		for (var k in this.paneVis) {
			this.paneVis[k] = num & this.paneVisBit[k] ? true : false;
		}
	},

	getPaneVisNum: function() {
		var num = 0;
		for (var k in this.paneVis) {
			if (this.paneVis[k]) {
				num += this.paneVisBit[k]
			}
		}
		return num;
	},

	isSingleColMode: function() {
		return !(this.paneVis.tabs && this.paneVis.list);
	},

	backToAgent: function() {
		if (window['DP_FRAME_OVERLAYS']) {
			for (var k in window['DP_FRAME_OVERLAYS']) {
				if (window['DP_FRAME_OVERLAYS'].hasOwnProperty(k) && window['DP_FRAME_OVERLAYS'][k].opened) {
					window['DP_FRAME_OVERLAYS'][k].close();
				}
			}
		}
	}
});
