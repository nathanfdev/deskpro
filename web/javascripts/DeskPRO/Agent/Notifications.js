Orb.createNamespace('DeskPRO.Agent');

DeskPRO.Agent.Notifications = new Orb.Class({
	Implements: [Orb.Util.Events, Orb.Util.Options],

	initialize: function() {
		var self = this;

		$('#dp_notif_bed, #notificationDropdown .notifHead').on('click', function(ev) {
			ev.preventDefault();
			ev.stopPropagation();

			self.toggle();
		});

		this.fireEvent('init');
		this._isOpen = false;

		DeskPRO_Window.getMessageBroker().addMessageListener('agent-notify.tickets', function(info) {
			this.addRow(info.row);
		}, this);

		$('#dp_notify_list').on('click', 'li[data-route]', function(ev) {
			ev.preventDefault();
			ev.stopPropagation();
			self.removeRow($(this));
			self.close();
			DeskPRO_Window.runPageRouteFromElement($(this));
		});
	},

	addRow: function(html_or_el) {
		var row = $(html_or_el);
		var type = row.data('type');

		$('time.timeago', row).text('').attr('datetime', (new Date()).toUTCString());
		DeskPRO_Window.initInterfaceServices(row);

		var ev = { row: row, type: type };
		this.fireEvent('addRow');

		this.modCount(type, '+');

		row.insertBefore('#dp_notify_list_dismiss');
	},

	addMessage: function(type, message, route) {
		var row = $('<li />');
		row.data('type', type);

		$('<em />').addClass('dismiss').appendTo(row);
		$('<time />').addClass('timeago').appendTo(row);
		$('<a />').text(message).data('route', route || '').appendTo(row);

		this.addRow(row);
	},

	removeRow: function(row) {
		var type = row.data('type');
		var ev = { row: row, type: type };
		this.fireEvent('addRow');

		this.modCount(type, '-');
		row.remove();
	},

	modCount: function(type, op, count) {
		var el   = $('#dp_notif_bed .notif-' + type);
		var el2  = $('#notificationDropdown .notif-' + type);

		var ev = { notif: this, type: type, op: op, count: count, el: el, el2: el2 };
		this.fireEvent('beforeModCount', ev);

		if (op == '=') {
			var newcount = count || 0;
			$('.counter', el).text(newcount);
			$('.counter', el2).text(newcount);
		} else {
			var newcount = parseInt(el.text().trim());
			if (op == '+') {
				newcount += (count || 1);
			} else {
				newcount -= (count || 1);
			}

			if (newcount < 0) newcount = 0;

			$('.counter', el).text(newcount || 0);
			$('.counter', el2).text(newcount || 0);
		}

		// <3 because the dismiss button and the help note are li's
		if ($('#dp_notify_list').find('> li').length < 3) {
			$('#dp_notify_list_none').hide();
			$('#dp_notify_list_dismiss').show();
		} else {
			$('#dp_notify_list_none').show();
			$('#dp_notify_list_dismiss').hide();
		}

		if (newcount < 1) {
			el.removeClass('with-count');
			el2.removeClass('with-count');
			this.fireEvent('typeHide', [type, el]);
		} else {
			el.addClass('with-count');
			el2.addClass('with-count');
			this.fireEvent('typeShow', [type, el]);
		}

		this.updatePositions();
		this.fireEvent('modCount', ev);
	},

	_lazyInitMenu: function() {
		if (this._hasInitMenu) return;
		this._hasInitMenu = true;

		var self = this;

		this.menu = $('#notificationDropdown').detach().appendTo('body');
		this.backdrop = $('<div class="backdrop" />').hide().appendTo('body');
		this.backdrop.on('click', function() {
			self.close();
		});

		this.menu.on('click', function(ev) {
			ev.preventDefault();
			ev.stopPropagation();
		});

		this.menu.on('click', '.dismiss', function(ev) {
			ev.preventDefault();
			ev.stopPropagation();

			var row = $(this).closest('li');
			self.removeRow(row);
		});

		$('#dp_notify_list_dismiss').on('click', function(ev) {
			ev.preventDefault();
			ev.stopPropagation();

			$('#dp_notify_list li').not('.dismissAll').remove();
			self.modCount('tickets', '=', 0);
			self.modCount('chat', '=', 0);
			self.close();
		});

		this.menu.on('click', '[data-route]', function(ev) {
			ev.stopPropagation();
			ev.preventDefault();

			self.removeRow($(this));
		});
	},

	open: function() {
		if (this._isOpen) return;
		this._isOpen = true;
		this._lazyInitMenu();
		this.menu.show();
		this.backdrop.show();
		this.updatePositions();
	},

	updatePositions: function() {
		if (!this._hasInitMenu) return;

		var pos = $('#dp_notif_bed').offset();
		this.menu.css({
			left: pos.left - $('#dp_notif_bed').width() + 56
		});
	},

	isOpen: function() {
		return this._isOpen;
	},

	close: function() {
		if (!this._isOpen) return;
		this._isOpen = false;
		this.menu.hide();
		this.backdrop.hide();
	},

	toggle: function() {
		if (this._isOpen) {
			this.close();
		} else {
			this.open();
		}
	},

	destroy: function() {
		if (this._hasInitMenu) {
			this.menu.remove();
			this.backdrop.remove();
		}
	}
});
