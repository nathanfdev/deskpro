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

		$('#dp_header_notify_wrap').on('click', '.trigger-dismiss', function(ev) {
			Orb.cancelEvent(ev);
			$(this).closest('.dp-header-notify-menu').find('li').each(function() {
				self.removeRow($(this));
			});
			Orb.shimClickCallbackPop();
		}).on('click', '.dismiss', function(ev) {
			Orb.cancelEvent(ev);
			ev.stopImmediatePropagation();

			var ul = $(this).closest('ul');
			self.removeRow($(this).closest('li'));

			if (!ul.find('li')[0]) {
				Orb.shimClickCallbackPop();
			}
		}).on('click', 'li.inside', function(ev) {
			Orb.cancelEvent(ev);
			ev.stopImmediatePropagation();

			DeskPRO_Window.runPageRouteFromElement($(this));

			var ul = $(this).closest('ul');
			self.removeRow($(this).closest('li'));

			if (!ul.find('li')[0]) {
				Orb.shimClickCallbackPop();
			}
		});

		this.fireEvent('init');
		this._isOpen = false;

		DeskPRO_Window.getMessageBroker().addMessageListener('agent-notify.tickets', function(info) { this.addRow(info.row); }, this);
		DeskPRO_Window.getMessageBroker().addMessageListener('agent-notify.tasks', function(info) { this.addRow(info.row); }, this);
		DeskPRO_Window.getMessageBroker().addMessageListener('agent-notify.new_comment', function(info) { this.addRow(info.row); }, this);
		DeskPRO_Window.getMessageBroker().addMessageListener('agent-notify.new_feedback', function(info) { this.addRow(info.row); }, this);
		DeskPRO_Window.getMessageBroker().addMessageListener('agent-notify.new_registration', function(info) { this.addRow(info.row); }, this);
		DeskPRO_Window.getMessageBroker().addMessageListener('agent-notify.twitter', function(info) { this.addRow(info.row); }, this);
	},

	getListTypeByType: function(type) {
		var listType = null;
		if (type == 'tickets') {
			listType = 'tickets';
		} else if (type == 'new_registration') {
			listType = 'people';
		} else if (type == 'chat') {
			listType = 'chat';
		} else if (type == 'tasks') {
			listType = 'tasks';
		} else if (type == 'new_comment' || type == 'new_feedback') {
			listType = 'publish';
		}

		return listType;
	},

	addRow: function(html_or_el) {
		var row = $(html_or_el);
		row.addClass('msg-row');
		row.data('route-notabreload', 1).attr('data-route-notabreload', 1);
		var type = row.data('type');

		if (type == 'chat') {
			return;
		}

		var listType = this.getListTypeByType(type);
		var list = $('#dp_header_notify_wrap').find('li.type-row.' + listType).find('ul.notify-list');

		var self = this;

		$('time.timeago', row).text('').attr('datetime', (new Date()).toISOString());
		DeskPRO_Window.initInterfaceServices(row);

		var ev = { row: row, type: type };
		this.fireEvent('addRow');

		list.prepend(row);

		this.modCount(type, '+');

		if (window.webkitNotifications && window.webkitNotifications.checkPermission() == 0) {

			var icon = row.data('icon') || '';
			if (icon) {
				icon = ASSETS_BASE_URL + '/' + icon;
			}

			var notification = window.webkitNotifications.createNotification(
				icon, row.find('a:first').text() || 'DeskPRO', row.find('.info').text()
			);
			notification.onclick = function() {
				window.focus();
				DeskPRO_Window.runPageRouteFromElement(row);
				self.removeRow(row);
			};
			notification.onclose = function() {
				if (!self._isRemoving && !row.data('notification-timeout')) {
					self.removeRow(row);
				}
			};
			if (DESKPRO_PERSON_NOTIFICATION_DISMISS) {
				notification.ondisplay = function() {
					setTimeout(function() {
						row.data('notification-timeout', true);
						notification.cancel();
					}, DESKPRO_PERSON_NOTIFICATION_DISMISS * 1000);
				};
			}
			notification.show();
			row.data('notification', notification);
		}
	},

	addMessage: function(type, message, route, id) {
		var row = $(DeskPRO_Window.util.getPlainTpl('#dp_header_notify_row_tpl'));
		row.data('type', type);
		row.addClass(type);
		row.data('data-route', route || '').attr('data-route', route || '')
			.data('route-notabreload', 1).attr('data-route-notabreload', 1);

		row.find('time').addClass('timeago').text('');
		row.find('big').text(message);

		if (id) {
			row.addClass('id-' + id);
		}

		this.addRow(row);
	},

	findRow: function(id_class) {
		var row = $('#dp_header_notify_wrap').find('li.' + id_class);

		if (!row[0]) {
			return null;
		}

		return row;
	},

	removeRow: function(row) {
		this._isRemoving = true;

		var type = row.data('type');
		var ev = { row: row, type: type };
		this.fireEvent('removeRow');

		if (row.data('notification')) {
			row.data('notification').close();
			row.data('notification', false);
		}

		row.remove();
		this.modCount(type, '-');

		if (row.data('class-id')) {
			var related = $('#dp_notify_list').find('li.' + row.data('class-id'));
			if (related[0]) {
				related.remove();
				this.modCount(type, '-', related.length);
			}
		}

		this._isRemoving = false;
	},

	removeRelated: function(related) {
		var self = this;

		$('#dp_header_notify_wrap').find('li').each(function() {
			var row = $(this);
			if (row.data('related') === related) {
				self.removeRow(row);
			}
		});
	},

	removeRowById: function(id) {
		var self = this;
		var row = $('#dp_header_notify_wrap').find('li.id-' + id);
		row.each(function() {
			self.removeRow($(this));
		});
	},

	removeRowByClass: function(id) {
		var self = this;
		var row = $('#dp_header_notify_wrap').find('li.' + id);
		row.each(function() {
			self.removeRow($(this));
		})
	},

	modCount: function(type, op, count) {
		var listType = this.getListTypeByType(type);
		if (!listType) return;

		var list = $('#dp_header_notify_wrap').find('li.type-row.' + listType);
		var el = list.find('.badge').first();

		var ev = { notif: this, type: type, op: op, count: count, el: el, el2: el };
		this.fireEvent('beforeModCount', ev);

		if (op == '=') {
			var newcount = count || 0;
			$('.counter', el).text(newcount);
		} else {
			var newcount = parseInt(el.text().trim());
			if (op == '+') {
				newcount += (count || 1);
			} else {
				newcount -= (count || 1);
			}

			if (newcount < 0) newcount = 0;

			el.text(newcount || 0);
		}

		// <3 because the dismiss button and the help note are li's
		if ($('#dp_notify_list').find('> li.msg-row').length) {
			$('#dp_notify_list_none').hide();
			$('#dp_notify_list_dismiss').show();
		} else {
			$('#dp_notify_list_none').show();
			$('#dp_notify_list_dismiss').hide();
		}

		if (newcount < 1) {
			el.hide();
			list.removeClass('dp-notifications-on');
			this.fireEvent('typeHide', [type, el]);
		} else {
			el.show();
			list.addClass('dp-notifications-on');
			this.fireEvent('typeShow', [type, el]);
		}

		this.updatePositions();
		this.fireEvent('modCount', ev);
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
			left: pos.left
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
