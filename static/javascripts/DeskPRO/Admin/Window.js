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

	initPage: function() {
		var self = this;

		this.menuEls = $('#menus_container > div').addClass('header-menu').each(function() {
			$(this).detach().appendTo('body');
		});
		$('#menus_container').remove();

		this.menuTriggerEls = $('#dp_admin_nav li[data-menu]');
		$('#dp_admin_nav').delegate('li[data-menu]', 'click', function(ev) {
			ev.preventDefault();
			self.openHeaderMenu($(this));
		});

		$('input[type="checkbox"].onoff-slider').checkbox({
			empty: ASSETS_BASE_URL + '/vendor/jquery/jquery-checkbox/empty.png'
		});

		$('table.with-reorderable').each(function() {
			var table = $(this);
			table.data('table-reorder', new DeskPRO.Admin.TableReorder(table));
		});

		if (typeof window.DeskPRO_Window_Init == 'function') {
			window.DeskPRO_Window_Init();
		}
	},

	openHeaderMenu: function(triggerEl) {
		if (!this.headerMenuBackdrop) {
			this.headerMenuBackdrop = $('<div class="backdrop" />').appendTo('body').hide();
			this.headerMenuBackdrop.click(this.closeHeaderMenu.bind(this));
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
	}
});
