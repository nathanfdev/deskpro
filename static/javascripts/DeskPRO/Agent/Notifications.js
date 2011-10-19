Orb.createNamespace('DeskPRO.Agent');

DeskPRO.Agent.Notifications = new Orb.Class({
	Implements: [Orb.Util.Events, Orb.Util.Options],

	initialize: function() {
		var self = this;

		$('#dp_notif_bed').click(function(ev) {
			ev.preventDefault();
			ev.stopPropagation();

			self.open();
		});

		this.fireEvent('init');

		DeskPRO_Window.getMessageBroker().addMessageListener('agent-notify.tickets', function(info) {
			console.log(info);
			this.addRow(info.row);
		}, this);
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

		if (newcount < 1) {
			el.hide();
			el2.hide();
			if (!$('#dp_notif_bed .notif-item:visible').length) {
				$('#dp_notif_bed').hide();
				this.close();
			}

			this.updatePositions();

			this.fireEvent('typeHide', [type, el]);
		} else {
			el.closest('.notif-item').show();
			el2.closest('.notif-item').show();
			$('#dp_notif_bed').show();

			this.updatePositions();

			this.fireEvent('typeShow', [type, el]);
		}

		this.fireEvent('modCount', ev);
	},

	_lazyInitMenu: function() {
		if (this._hasInitMenu) return;
		this._hasInitMenu = true;

		var self = this;

		this.menu = $('#notificationDropdown').detach().appendTo('body');
		this.backdrop = $('<div class="backdrop" />').hide().appendTo('body');
		this.backdrop.click(function() {
			self.close();
		});

		this.menu.click(function(ev) {
			ev.preventDefault();
			ev.stopPropagation();
		});

		this.menu.delegate('.dismiss', 'click', function(ev) {
			ev.preventDefault();
			ev.stopPropagation();

			var row = $(this).closest('li');
			self.removeRow(row);
		});

		$('#dp_notify_list_dismiss').click(function(ev) {
			ev.preventDefault();
			ev.stopPropagation();

			$('#dp_notify_list li').not('.dismissAll').remove();
			self.modCount('tickets', '=', 0);
			self.modCount('chat', '=', 0);
			self.close();
		});

		this.menu.delegate('[data-route]', 'click', function(ev) {
			ev.stopPropagation();
			ev.preventDefault();

			self.removeRow($(this));
		});
	},

	open: function() {
		this._lazyInitMenu();
		this.menu.show();
		this.backdrop.show();
		this.updatePositions();
	},

	updatePositions: function() {
		if (!this._hasInitMenu) return;

		var pos = $('#dp_notif_bed').offset();
		this.menu.css({
			left: pos.left
		});
	},

	isOpen: function() {
		if (this._hasInitMenu && this.menu.is(':visible')) {
			return true;
		}

		return false;
	},

	close: function() {
		if (!this.isOpen()) return;
		this.menu.hide();
		this.backdrop.hide();
	},

	destroy: function() {
		if (this._hasInitMenu) {
			this.menu.remove();
			this.backdrop.remove();
		}
	}
});
