Orb.createNamespace('DeskPRO.Admin');

/**
 * Similar in responsibility to the Agent.Window (global object/init etc), but completely
 * different.
 *
 * Within the admin interface, this is the gloabl var DeskPRO_Window.
 *
 * If a page defines the special function DeskPRO_Window_Init(), it will be called
 * automatically once the page is ready.
 */
DeskPRO.Admin.Window = new Orb.Class({

	Extends: DeskPRO.BasicWindow,

	init: function() {
		var self = this;
		this.util = {
			modCountEl: function(el, op, num) {

				el = $(el);

				if (!num) num = 1;

				var count = parseInt(el.text().trim());

				if (op == '-' || op == 'rem' || op == 'del' || op == 'sub') {
					count -= num;
					if (count < 0) count = 0;
				} else if (op == '+' || op == 'add') {
					count += num;
				} else {
					count = num;
				}

				el.text(count);

				return count;
			},

			showSavePuff: function(overEl) {
				var el = $('<div class="load-puff" style="display: none; opacity: 0" />');
				el.appendTo('body');

				var pos = overEl.offset();
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

			/**
			 * Get a "plain" article. ie of type="text/x-deskpro-plain"
			 *
			 * @param el
			 * @return {String}
			 */
			getPlainTpl: function(el) {
				var el = $(el);
				var html = el.get(0).innerHTML;

				html = html.replace(/%startScript%/g, '<script>');
				html = html.replace(/%endScript%/g, '</script>');

				return html;
			}
		};

		$(window).on('resize', function() {
			self.updatePageNavPos();
		});

		$(document).ajaxError(this.ajaxGlobalErrorHandler.bind(this));
	},

	ajaxGlobalErrorHandler: function(event, xhr, ajaxOptions, errorThrown, force) {
		// Session timed out / cookies cleared
		if (xhr && xhr.status && xhr.status == '403') {
			window.location = BASE_URL + 'admin/';
		}
	},

	initPage: function() {
		var self = this;

		var side = $('#dp_admin_page_sidebar, #dp_admin_page_sidebar_right').first();
		if (side.length) {
			$('#dp_admin_page_inner').css('min-height', side.outerHeight() + 150);
		}

		this.menuEls = $('#menus_container > div').addClass('header-menu').each(function() {
			$(this).detach().appendTo('body');
		});
		$('#menus_container').remove();

		this.menuTriggerEls = $('#dp_admin_nav li[data-menu]');
		$('#dp_admin_nav').on('click', 'li[data-menu]', function(ev) {
			ev.preventDefault();
			self.openHeaderMenu($(this));
		});

		$(':checkbox.onoff-slider').checkbox({
			empty: ASSETS_BASE_URL + '/vendor/jquery/jquery-checkbox/empty.png'
		});

		$('table.with-reorderable').each(function() {
			var table = $(this);
			table.data('table-reorder', new DeskPRO.Admin.TableReorder(table));
		});

		// Interface toggle
		$('#DP-InterfaceSwitcher > .DP-adminSwitch > .adminSwitcher').on('click', function(ev) {
			ev.preventDefault();
			ev.stopPropagation();

			var pos = $(this).offset();
			var w = $(this).outerWidth();
			var h= $(this).outerHeight();

			var list = $('#interfacesToggle');
			list.hide().detach().appendTo('body');
			list.css({
				top: pos.top,
				left: pos.left
			});
			list.show();

			list.find('a').on('click', function() { list.hide(); backdrop.remove(); });

			var backdrop = $('<div class="backdrop" />').appendTo('body');
			backdrop.on('click', function() {
				list.hide();
				backdrop.remove();
			});
		});

		$(document).on('mouseover', '.tipped', function() {
			if ($(this).is('.tipped-inited')) {
				return;
			}
			var options = {};
			if ($(this).data('tipped-options')) {
				eval('options = {' + $(this).data('tipped-options') + '}');
			}

			Tipped.create(this, $(this).data('tipped') || $(this).attr('title'), options);
			$(this).addClass('tipped-inited');
		});

		jQuery.extend(Tipped.Skins, {
			'dperror' : {
				border: { size: 1, color: '#CF2020' },
				background: '#F3DDDE',
				radius: { size: 1, position: 'border' },
				shadow: true,
				closeButtonSkin: 'light'
			}
		});
		$('.tipped-click').each(function() {
			var target = $(this);
			if (target.data('tipped-target')) {
				target = target.find(target.data('tipped-target'));
			}

			if (target.is('.tipped-inited')) {
				return;
			}
			var options = {};
			if ($(this).data('tipped-options')) {
				eval('options = {' + $(this).data('tipped-options') + '}');
			}
			options.showOn = false;
			options.hideOn = 'click-outside';

			var id = target.attr('id');
			if (!id) {
				id = Orb.getUniqueId('tipped');
				target.attr('id', id);
			}

			Tipped.create('#' + id, $(this).data('tipped') || $(this).attr('title'), options);
			$(this).attr('title', '');
			target.addClass('tipped-inited');

			target.on('click', function(ev) {
				ev.preventDefault();
				ev.stopPropagation();
				ev.stopImmediatePropagation();
				Tipped.show('#' + id);
			});
		});

		$(document).on('click', '.click-go', function(ev) {
			var url = $(this).data('url');
			if (url) {
				window.location = url;
				ev.preventDefault();
				ev.stopPropagation();
			}
		});

		DeskPRO.ElementHandler_Exec();

		$('time.timeago').timeago();

		if (typeof window.DeskPRO_Window_Init == 'function') {
			window.DeskPRO_Window_Init();
		}

		if (document.getElementById('dp_page_nav')) {
			$('#dp_admin_page').css('min-height', $('#dp_page_nav').outerHeight() + 10);
			if ($('#dp_page_nav').hasClass('fixed')) {
				this.updatePageNavPos();
			} else {
				window.setTimeout(this.updatePageNavPos.bind(this), 30);
				$(window).scroll(this.updatePageNavPos.bind(this));
				$(window).on('resize', this.updatePageNavPos.bind(this));
			}
		}

		$('#portal_nav').on('click', 'li', function(ev) {
			if ($(ev.target).is('a')) {
				return;
			}
			var a = $('a', this).first();
			if (!a.attr('href')) {
				return;
			}
			window.location = a.attr('href');
		});

		this._initHelp();

		// User menu
		$('#userSetting_trigger').on('click', function(ev) {
			ev.preventDefault();
			ev.stopPropagation();

			var list = $('#userSetting');
			list.hide().detach().appendTo('body');

			var left = $('#userSetting_trigger').offset().left;
			left -= 135;

			list.css({
				top: 41,
				left: left
			});
			list.show();

			var backdrop = $('<div class="backdrop" />').appendTo('body');

			var close = function() {
				list.hide();
				backdrop.remove();
			};
			backdrop.on('click', close);
			list.on('click', close);
			$('ul', list).on('click', function(ev) {
				ev.stopPropagation();
			});
		});

		$('.confirm-delete-trigger').on('click', function(ev) {
			var message = $(this).data('prompt');
			if (!message) {
				message = 'Are you sure you want to delete this?';
			}

			if (!confirm(message)) {
				ev.preventDefault();
			}
		});

		// DeskPRO logo menu
		var logoBackdrop = null;
		$('#dp_logo_wrap .button-wrap').on('click', function(ev) {
			ev.preventDefault();
			ev.stopPropagation();
			var left = $('#dp_logo_wrap .button-wrap').offset().left + 51;
			$('#dp_logo_expand_wrap').detach().appendTo('body').css('left', left).css('right', 'auto').show();
			if (!logoBackdrop) {
				logoBackdrop = $('<div class="backdrop" />').appendTo('body');
				logoBackdrop.click(function() { logoBackdrop.hide();$('#dp_logo_expand_wrap').hide(); });
			}
			logoBackdrop.show();
		});
		$('#dp_logo_expand_wrap').on('click', function(ev) {
			ev.stopPropagation();
			$('#dp_logo_expand_wrap').hide();
			if (logoBackdrop) logoBackdrop.hide();
		});

		// User menu
		$('#userSetting_trigger').on('click', function(ev) {
			ev.preventDefault();
			ev.stopPropagation();

			var list = $('#userSetting');
			list.hide().detach().appendTo('body');
			list.css({
				top: 41,
				left: $('#userSetting_trigger').offset().left + 1
			});
			list.show();

			var backdrop = $('<div class="backdrop" />').appendTo('body');

			var close = function() {
				list.hide();
				backdrop.remove();
			};
			backdrop.on('click', close);
			list.on('click', close);
			$('ul', list).on('click', function(ev) {
				ev.stopPropagation();
			});
		});

		var newDashOverlay = new DeskPRO.UI.Overlay({
			triggerElement: '#new_dashboard_trigger',
			contentElement: '#new_dashboard_overlay'
		});

		$('form.with-form-validator').each(function() {
			var v = new DeskPRO.Form.FormValidator($(this));
			$(this).data('form-validator-inst', v);
		});
	},

	updatePageNavPos: function() {

		var nav  = $('#dp_page_nav');

		var page = $('#dp_admin_page');
		var mode = 'normal';

		if (!page.length) {
			page = $('#dp_fauxbrowser');
			//nav.addClass('fauxbrowser');
			//var mode = 'alt';
		}

		var winWidth = $(window).width();
		var navWidth = 175 + (mode=='alt' ? 35 : 0); // width of the nav, minus the few pixels of overlap
		var pageWidth = 971;
		var totalWidth = pageWidth+navWidth; // 971 is width of page, aka $('#dp_header').outerWidth();

		var workingWidth = winWidth;

		// If its over, force a scroll
		if (totalWidth > winWidth) {
			$('body').css({'padding-left': navWidth, 'min-width': 971});
		} else {
			$('body').css({width: 'auto'});
			// The space naturally available to the left
			var spaceAvail = (workingWidth - pageWidth) / 2;
			if (spaceAvail < navWidth) {
				$('body').css('padding-left', navWidth);
			} else {
				$('body').css('padding-left', 0);
			}
		}

		var top = page.offset().top + 15 + (mode=='alt' ? 35 : 0);
		top += $(window).scrollTop();

		var left = page.offset().left;

		nav.css({
			top: top,
			left: left - nav.outerWidth() + 5 - (mode=='alt' ? 35 : 0)
		});

		if (nav.height() > page.height()) {
			$('.dp-page-box', page).last().css('min-height', nav.height() + 100);
		}

		nav.css('visibility', 'visible');
	},

	openHeaderMenu: function(triggerEl) {
		if (!this.headerMenuBackdrop) {
			this.headerMenuBackdrop = $('<div class="backdrop" />').appendTo('body').hide();
			this.headerMenuBackdrop.on('click', this.closeHeaderMenu.bind(this));
		}

		this.menuTriggerEls.removeClass('open');
		this.menuEls.hide();

		var pos = triggerEl.offset();
		var h = triggerEl.outerHeight();
		var menuEl = $(triggerEl.data('menu'));

		triggerEl.addClass('open')
		menuEl.css({
			top: pos.top + h,
			left: pos.left
		});

		if (!menuEl.hasClass('has-init')) {
			menuEl.addClass('has-init');
			menuEl.find('li').on('click', function() {
				window.location = $(this).find('a').first().attr('href');
			})
		}

		menuEl.show();
		triggerEl.show();
		this.headerMenuBackdrop.show();
	},

	closeHeaderMenu: function() {
		this.menuTriggerEls.removeClass('open');
		var vis = this.menuEls.filter(':visible');
		vis.fadeOut('fast');
		this.headerMenuBackdrop.hide();
	},

	/**
	 * Dismiss a help message. This removes the help element, and sends an ajax
	 * request to the server to record the dismiss so it doesnt show again.
	 *
	 * The element must have a data-message-id attribute.
	 *
	 * @param el
	 */
	dismissHelpMessage: function(el) {
		el = $(el);

		var messageId = el.data('message-id');

		el.remove();

		if (!messageId) {
			return;
		}

		$.ajax({
			dataType: 'json',
			url: BASE_URL + 'agent/misc/dismiss-help-message/' + escape(messageId),
			type: 'GET'
		});
	},


	_initHelp: function() {
		this.helpBtn = $('#dp_pagehelp_btn');
		this.helpBox = $('#dp_pagehelp');

		if (!this.helpBox.length) {
			return;
		}

		if (this.helpBox.is(':visible')) {
			this.helpBtn.addClass('on');
		}

		this.helpBtn.on('click', function() {

		});
	}
});
