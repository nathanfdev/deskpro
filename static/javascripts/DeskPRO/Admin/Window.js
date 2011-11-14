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
		this.util = {
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
	},

	initPage: function() {
		var self = this;

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

			var backdrop = $('<div class="backdrop" />').appendTo('body');
			backdrop.on('click', function() {
				list.hide();
				backdrop.remove();
			});
		});

		this.initFeatures();

		DeskPRO.ElementHandler_Exec();

		if (typeof window.DeskPRO_Window_Init == 'function') {
			window.DeskPRO_Window_Init();
		}

		if (document.getElementById('dp_page_nav')) {
			window.setTimeout(this.updatePageNavPos.bind(this), 30);
			$(window).scroll(this.updatePageNavPos.bind(this));
			$(window).on('resize', this.updatePageNavPos.bind(this));
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
	},

	updatePageNavPos: function() {
		var page = $('#dp_admin_page');
		var nav  = $('#dp_page_nav');

		var top = page.offset().top + 15;
		top += $(window).scrollTop();

		var left = page.offset().left;

		nav.css({
			top: top,
			left: left - nav.outerWidth() + 5
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

	initFeatures: function(contextEl) {
		var self = this;

		DeskPRO.ElementHandler_Exec();

		$('.timeago').timeago();
	}
});
