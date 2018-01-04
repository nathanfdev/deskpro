Orb.createNamespace('DeskPRO.Agent');

DeskPRO.Agent.Notifications = new Orb.Class({
	Implements: [Orb.Util.Events, Orb.Util.Options],

	initialize: function() {
		var self = this;

		this.fireEvent('init');
		this.waitingRows = [];

		this.flushAddRows = _.throttle(this.flushAddRowsNow.bind(this), 750, {leading: false});

		this.dismissedIds = [];
		if (Modernizr.localstorage && window.localStorage['dpa_dissmissalerts']) {
			this.dismissedIds = window.localStorage['dpa_dissmissalerts'].split(',');
			for(var i=0; i<this.dismissedIds.length; i++) { this.dismissedIds[i] = parseInt(this.dismissedIds[i], 10); }
		}

		DeskPRO_Window.getMessageBroker().addMessageListener('agent-notify.tickets', function(info) { this.addRow(info.row, info.alert_id || null, info.icon || null); }, this);
		DeskPRO_Window.getMessageBroker().addMessageListener('agent-notify.tasks', function(info) { this.addRow(info.row); }, this);
		DeskPRO_Window.getMessageBroker().addMessageListener('agent-notify.new_comment', function(info) { this.addRow(info.row); }, this);
		DeskPRO_Window.getMessageBroker().addMessageListener('agent-notify.new_feedback', function(info) { this.addRow(info.row); }, this);
		DeskPRO_Window.getMessageBroker().addMessageListener('agent-notify.new_registration', function(info) { this.addRow(info.row); }, this);

		var notifyBox = $('#dp_header_notify_wrap');
		this.notifyBox = notifyBox;
		this.notifsBadge = $('#notifs_counts');
		this.notifBtn = $('#notifs_btn');

		notifyBox.on('click', '.trigger-dismiss', function(ev) {
			Orb.cancelEvent(ev);
			self.dismissAll();
		}).on('click', '.dismiss', function(ev) {

			if ($('#dp_notify_list_main').find('li') == 1) {
				$('#dp_header_notify_wrap').trigger('dpClose');
			}

			Orb.cancelEvent(ev);
			ev.stopImmediatePropagation();

			var ul = $(this).closest('ul');
			var row = $(this).closest('li');

			self.removeRow(row);

			if (row.data('alert-id')) {
				self.dismissAlertId(row.data('alert-id'));
			}

		}).on('click', 'li.inside', function(ev) {
			Orb.cancelEvent(ev);
			ev.stopImmediatePropagation();

			var row = $(this).closest('li');

			if (row.hasClass('is-dismissed')) {
				DeskPRO_Window.runPageRouteFromElement($(this));
				return;
			}

			if ($('#dp_notify_list_main').find('li') == 1) {
				$('#dp_header_notify_wrap').trigger('dpClose');
			}

			var ul = $(this).closest('ul');

			if (row.data('alert-id')) {
				self.dismissAlertId(row.data('alert-id'));
				DeskPRO_Window.getMessageChanneler().poller.send();
			}

			self.removeRow(row);

			DeskPRO_Window.runPageRouteFromElement($(this));

		}).on('click', '.trigger-notify-prefs', function(ev) {
			Orb.cancelEvent(ev);
			ev.stopImmediatePropagation();
			$('#dp_header_notify_wrap').trigger('dpClose');
			$('#settingswin').trigger('dp_open', 'ticket-notify');
		}).on('click', '.see_dismissed', function(e) {

			var type = 'main';

			Orb.cancelEvent(e);

			notifyBox.find('a.see_current').removeClass('selected');
			$(this).addClass('selected');
			notifyBox.removeClass('mode-current').addClass('mode-dismissed');

			notifyBox.find('.notify-list.for-current').hide();
			var ul = notifyBox.find('.notify-list.for-dismissed');
			ul.empty();

			$("#dp_header_notify_wrap").find('footer.for-current').hide();

			notifyBox.find(".no-notifications").hide();
			notifyBox.find(".notification-progress-on").show();
			$.ajax({
				url: BASE_PATH + 'get_messages.php',
				data: {dismissed: true},
				type: 'get',
				dataType: 'json',
				success: function(rows) {
					notifyBox.find(".notification-progress-on").hide();
					ul.html(rows.rendered_list);
					ul.find('.dismiss').remove();
					ul.find('li').addClass('is-dismissed');
					ul.find('time').addClass('timeago');
					ul.show();
				}
			});
		}).on('click', '.see_current', function(e) {
			$(this).addClass('selected');
			self.resetElements();
		});
	},

	resetElements: function() {
		var wrap = $('#dp_header_notify_wrap');

		var notifUl = wrap.find('.notify-list.for-current')
		wrap.find('a.see_dismissed').removeClass('selected');
		wrap.find('a.see_current').addClass('selected');

		wrap.removeClass('mode-dismissed').addClass('mode-current');

		wrap.find(".no-notifications").hide();
		wrap.find('.notify-list.for-dismissed').empty().hide();

		if (!notifUl.find('li')[0]) {
			wrap.find(".no-notifications").not(".notification-progress-on").show();
			$("#dp_notify_wrap").find('.notify-list.for-current').hide();
			wrap.find('footer.for-current').hide();
		} else {
			notifUl.show();
			wrap.find('footer.for-current').show();
		}
	},

	dismissAll: function() {

		$('#dp_header_notify_wrap').trigger('dpClose');

		var self = this;
		this.notifyBox.find('.notify-list.for-current').each(function() {
			$(this).find('li').each(function() {
				var row = $(this);
				self.removeRow(row, true);
			});
		});

		DeskPRO_Window.dismissAlertQueue = [-1];
		DeskPRO_Window.getMessageChanneler().poller.send();
	},

	dismissAlertId: function(alertId) {

		alertId = parseInt(alertId);
		this.dismissedIds.include(alertId);
		DeskPRO_Window.dismissAlertQueue.push(alertId);

		if (this.dismissedIds.length > 1000) {
			while (this.dismissedIds.length > 1000) {
				this.dismissedIds.shift();
			}
		}

		if (Modernizr.localstorage) {
			window.localStorage['dpa_dissmissalerts'] = this.dismissedIds.join(',');
		}

		if ($('#dp_notify_list_main').find('li') < 1) {
			DeskPRO_Window.dismissAlertQueue = [-1];
			$('#dp_header_notify_wrap').trigger('dpClose');
		}
	},

	isDismissedAlready: function(alertId) {
		alertId = parseInt(alertId);
		return this.dismissedIds.indexOf(alertId) !== -1;
	},

	flushAddRowsNow: function() {
    var self = this;
		var rows = this.waitingRows;
		this.waitingRows = [];

		var modTypes = [];
		var appendRows = [];

		rows.forEach((function(r) {
			var html_or_el = r[0],
				alert_id = r[1],
				avatar = r[2],
				sendBrowserNotif = r[3];

      if (alert_id && this.isDismissedAlready(alert_id)) {
        this.dismissAlertId(alert_id);
        return;
      }

      var row = $(html_or_el);
      row.addClass('msg-row');
      row.data('route-notabreload', 1).attr('data-route-notabreload', 1);
      row.data('avatar', avatar);

      if (!sendBrowserNotif) {
      	row.data('no-browser-notif', true);
			}

      if (alert_id) {
        row.data('alert-id', alert_id);
        row.attr('data-alert-id', alert_id);
      }

      var type = row.data('type');

      if (type == 'chat') {
        return;
      }

      appendRows.push(row.get(0));
      modTypes.push(type);
		}).bind(this));

		var runNotifs = function() {
      var list = $('#dp_notify_list_main');
      appendRows.reverse();
      appendRows = $(appendRows);

      var nowString = (new Date()).toISOString();
      appendRows.each(function() {
        var time = $(this).find('time').text('now');
        if (time[0]) {
          if (!time.attr('datetime')) {
            time.attr('datetime', nowString);
          }
        }
			});

      list.prepend(appendRows);

      if (DeskPRO_Window.getMessageChanneler().hasDoneInitialLoad) {
        appendRows.each(function() {
        	var row = $(this);

        	if (row.data('no-browser-notif')) {
        		return;
					}

          var icon = row.data('icon') || '';
          if (icon) {
            icon = ASSETS_BASE_URL + '/' + icon;
          }
          icon = icon || row.data('avatar');

          var title = $.trim(row.find('big').first().text().replace(/[\n]/g, ' ').replace(/\s+/g, ' ')).replace(/^#\d+\s*/, '');
          var body = $.trim(row.find('small').first().text().replace(/[\n]/g, ' ').replace(/\s+/g, ' '));

          var Notify = window.Notify.default;
          var notification = new Notify(title || 'DeskPRO', {
            body: body,
            icon: icon,
            notifyClick: function () {
              window.focus();
              DeskPRO_Window.runPageRouteFromElement(row);
              self.removeRow(row);
            },
            // don't allow browser to close notification automatically, notification should remain available
            // until the user activates or dismisses the notification or we close by timeout
            // https://notifications.spec.whatwg.org/#require-interaction-preference-flag
            requireInteraction: true
          });

          if (Notify.isSupported && !Notify.needsPermission) {
            notification.show();
            if (DESKPRO_PERSON_NOTIFICATION_DISMISS) {
              // Notify options `requireInteraction` and `timeout` are mutually exclusive
              // have to manually initialize close by timeout
              setTimeout(notification.close.bind(notification), DESKPRO_PERSON_NOTIFICATION_DISMISS * 1000);
            }
            row.data('notification', notification);
          }
        });
      }

      modTypes.map(function(type) {
        self.modCount(type, '+');
      });
		};

    window.requestIdleCallback ?
      window.requestIdleCallback(runNotifs, {timeout: 2000}) :
      window.setTimeout(runNotifs, 250);
	},

	addRow: function(html_or_el, alert_id, avatar) {
		this.waitingRows.push([html_or_el, alert_id, avatar, DeskPRO_Window.getMessageChanneler().hasDoneInitialLoad]);
    this.flushAddRows();
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

	removeRow: function(row, noSendUpdate) {
		this._isRemoving = true;

		var self = this;
		var type = 'main';
		var ev = { row: row, type: type };
		var any_alert_ids = false;
		this.fireEvent('removeRow');

		if (row.data('notification')) {
			// Depending on which api is being imlpemented by the browser, it could be close or cancel
			if (row.data('notification').close) {
				try { row.data('notification').close(); } catch (e) {}
			}
			if (row.data('notification').cancel) {
				try { row.data('notification').cancel(); } catch (e) {}
			}
			row.data('notification', false);
		}

		row.remove();
		this.modCount(type, '-');

		if (row.data('class-id')) {
			var related = $('#dp_notify_list_' + type).find('li.' + row.data('class-id'));
			related.each(function() {
				var $related = $(this);
				$related.remove();

				if ($related.data('alert-id')) {
					any_alert_ids = true;
					self.dismissAlertId($related.data('alert-id'));
				}
			});
			this.modCount(type, '-', related.length);
		}

		if (row.data('alert-id')) {
			any_alert_ids = true;
			this.dismissAlertId(row.data('alert-id'));
		}

		if (!noSendUpdate && any_alert_ids) {
			if ($('#dp_notify_list_main').find('li') < 1) {
				this.dismissAll();
			} else {
				DeskPRO_Window.getMessageChanneler().poller.send();
			}
		}

		this._isRemoving = false;
	},

	removeRelated: function(related) {
		var self = this;
		var any = false;

		$('.notify-list.for-current').find('li').each(function() {
			var row = $(this);
			if (row.data('related') === related) {
				any = true;
				self.removeRow(row, true);
			}
		});

		if (any) {
			DeskPRO_Window.getMessageChanneler().poller.send();
		}
	},

	removeRowById: function(id) {
		var self = this;
		var row = $('.notify-list.for-current').find('li.id-' + id);
		row.each(function() {
			self.removeRow($(this), true);
		});
	},

	removeRowByClass: function(id) {
		var self = this;
		var row = $('.notify-list.for-current').find('li.' + id);
		row.each(function() {
			self.removeRow($(this), true);
		});
	},

	modCount: function(type, op, count) {
		var list = $('#dp_notify_list_main');
		var newcount = list.find('li').length;

		var ev = { notif: this, type: type, op: op, count: newcount };
		this.fireEvent('beforeModCount', [ev]);

		var event = new CustomEvent('dpUpdateNotifCount', { 'detail': ev });
		window.document.dispatchEvent(event);

		this.notifsBadge.text(newcount).data('count', newcount);

		if (newcount < 1) {
			$('#dp_header_notify_wrap').trigger('dpClose');

			this.notifBtn.removeClass('with-activity');
      list.hide();
			this.notifyBox.find('.no-notifications.for-current').show();

			if (!this.notifyBox.find('.dp-notifications-on')[0]) {
				this.notifyBox.find('li.none').show();
			}

			$("#dp_header_notify_wrap").find('footer.for-current').hide();
		} else {
			this.notifBtn.addClass('with-activity');
			this.notifyBox.addClass('dp-notifications-on');
			this.fireEvent('typeShow', [type]);
			this.notifyBox.find('li.none').hide();
			this.notifyBox.find('.no-notifications').hide();
      list.show();
		}

		$('#dp_header_notify_wrap').find('.notify-count').text(newcount);

		this.fireEvent('modCount', [ev]);
	}
});
