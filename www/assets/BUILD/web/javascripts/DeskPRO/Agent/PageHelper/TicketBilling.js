Orb.createNamespace('DeskPRO.Agent.PageHelper');

DeskPRO.Agent.PageHelper.TicketBilling = new Orb.Class({
	Implements: [Orb.Util.Events, Orb.Util.Options],

	initialize: function(wrap, baseId, options) {
		this.baseId = baseId;
		this.options = {
			auto_start_bill: false,
      onBeforeBillingChange: function (changeType, charge, chargeFormData) {},
      onAfterBillingChange: function (status, changeType, charge, chargeFormData) {}
		};

		this.setOptions(options);

		this.billingStart = false;
		this.billingExtraTime = 0;
		this.billingTimer = null;

		var self = this;

		this.hasBilling = (wrap.length > 0);

		if (!wrap.length) {
			return;
		}

    var initDateTimePicker = function(){
      $('input.DateTime:not(.datetimepickerinit), .DateTime input:not(.datetimepickerinit)', wrap).each(function () {
        $(this).addClass('datetimepickerinit');
        $(this).datetimepicker({
          format: 'YYYY-MM-DD HH:mm',
          widgetParent: $(this).parent().css('position', 'relative'),
          icons: {
            time: 'far fa-clock',
            date: 'far fa-calendar',
            up: 'fas fa-chevron-up',
            down: 'fas fa-chevron-down',
            previous: 'fas fa-chevron-left',
            next: 'fas fa-chevron-right'
          }
        });
				$(this).on('dp.change', function(){
					$(this).trigger('change');
				});
      });

      $('.Date input:not(.datetimepickerinit)', wrap).each(function () {
        $(this).addClass('datetimepickerinit');
        $(this).datetimepicker({
          format: 'YYYY-MM-DD',
          widgetParent: $(this).parent().css('position', 'relative'),
          icons: {
            time: 'far fa-clock',
            date: 'far fa-calendar',
            up: 'fas fa-chevron-up',
            down: 'fas fa-chevron-down',
            previous: 'fas fa-chevron-left',
            next: 'fas fa-chevron-right'
          }
        });
				$(this).on('dp.change', function(){
					$(this).trigger('change');
				});
      });
    };

		var form = this.getEl('billing_form');
		var progress = this.getEl('billing_save_progress');
		var typeInputs = form.find('input[name=' + this.baseId + '_billing_type]');
		var billingRows = this.getEl('billing_rows');

		this.doInitForm = (function() {
			form = this.getEl('billing_form');
			progress = this.getEl('billing_save_progress');
			typeInputs = form.find('input[name=' + this.baseId + '_billing_type]');

			typeInputs.change(function () {
				self.updateBillingForm(true);
			});
			this.updateBillingForm(true);

			this.getEl('billing_stop').click(function () {
				self.stopBillingTimer(false);
				$(this).hide();
				self.getEl('billing_start').show();
			});
			this.getEl('billing_start').click(function () {
				if (self.getBillingType() !== 'time') {
					return;
				}

				self.startBillingTimer(false);
				$(this).hide();
				self.getEl('billing_stop').show();
			});
			this.getEl('billing_reset').click(function () {
				if (self.getBillingType() === 'time') {
					if (self.getEl('billing_stop').is(':visible')) {
						self.startBillingTimer(true);
					} else {
						self.stopBillingTimer(true);
					}
				} else {
					self.stopBillingTimer(true);
				}
			});

			this.getEl('billing_save').click(function () {
				progress.show();
				var $err = self.getEl('billing_save_errors').hide()
					, id   = $(this).data('charge-id')
					;
				var formData = self.getFormData();

				var tmpId = 'tmp-' + Math.random().toString(36);
        self.fireEvent('onBeforeBillingChange', ['add', {id: 'tmpId'}, formData]);

				$.ajax({
					url:      $(this).data('submit-url'),
					data:     formData,
					type:     'POST',
					dataType: 'json'
				}).done(function (json) {
					if (json.inserted && self.addBillingRow) {
            self.fireEvent('onAfterBillingChange', ['success', 'add', {id: 'tmpId'}, formData]);
						self.addBillingRow(json.html);
						self.resetBillingForm();
					} else if (json.invalid_custom_fields) {
            self.fireEvent('onAfterBillingChange', ['failure', 'add', {id: 'tmpId'}, formData]);
						$err.children().remove();
						for (var i in json.invalid_custom_fields) {
							$err.append('<li>' + json.invalid_custom_fields[i] + '</li>');
						}
						$err.show();
					}
				}).always(function () {
					progress.hide();
				});
			});

      initDateTimePicker();
		}).bind(this);

		this.initForm();

		wrap.on('click', 'a.billing-delete', function(e) {
			var id = $(this).data('charge-id')
        , table = $(this).closest('table')
        ;
			e.preventDefault();

			if (confirm(billingRows.data('delete-confirm'))) {
        self.fireEvent('onBeforeBillingChange', ['delete', {id: id}]);
				$.ajax({
					url: $(this).attr('href'),
					type: 'POST',
					dataType: 'json'
				}).done(function (json) {
					if (json.success) {
            self.fireEvent('onAfterBillingChange', ['success', 'delete', {id: id}]);
						wrap.find('tr.ticket-charge-edit-' + id + ', tr#ticket-charge-row-' + id + ', tr.ticket-charge-edit-errors-' + id).remove();
						if (!table.find('tbody tr').length) {
							table.hide();
						}
					} else {
            self.fireEvent('onAfterBillingChange', ['failure', 'delete', {id: id}]);
          }
				});
			}
		});

		wrap.on('click', 'a.billing-edit', function(e) {
			e.preventDefault();
      var id = $(this).data('charge-id')
        , charge = wrap.find('tr#ticket-charge-row-' + id).hide().data('charge')
        ;
      self.populateForm(wrap.find('.ticket-charge-edit-' + id).show(), charge);
			return false;
		});

		wrap.on('click', 'a.billing-edit-discard', function(e) {
			e.preventDefault();
      var id = $(this).data('charge-id');
      wrap.find('tr#ticket-charge-row-' + id).show();
      wrap.find('tr.ticket-charge-edit-' + id + ', tr.ticket-charge-edit-errors-' + id).hide();
			return false;
		});

		wrap.on('click', 'a.billing-edit-save', function(e) {
			e.preventDefault();
			var id = $(this).data('charge-id')
        , $form = wrap.find('.ticket-charge-edit-' + id)
        ;
			wrap.find('.ticket-charge-edit-errors-' + id).hide();

			var formData = self.getFormData($form);
      self.fireEvent('onBeforeBillingChange', ['update', {id: id}, formData]);

			$.ajax({
				url: $(this).attr('href'),
				data: formData,
				type: 'POST',
				dataType: 'json'
			}).done(function(json) {
				if (json.updated && json.html) {
          self.fireEvent('onAfterBillingChange', ['success', 'update', {id: id}], formData);
					wrap.find('tr.ticket-charge-edit-' + id + ', tr.ticket-charge-edit-errors-' + id).remove();
					wrap.find('tr#ticket-charge-row-' + id).replaceWith(json.html);
          initDateTimePicker();
				}else if(json.invalid_custom_fields) {
          self.fireEvent('onAfterBillingChange', ['failure', 'update', {id: id}], formData);
					var $err = wrap.find('.ticket-charge-edit-errors-' + id).show().find('.form-errors');
					$err.children().remove();
					for (var i in json.invalid_custom_fields) {
						$err.append('<li>' + json.invalid_custom_fields[i] + '</li>');
					}
          $err.show();
				}

			}).always(function() {
				progress.hide();
			});

			return false;
		});

		if (this.options.auto_start_bill) {
			this.getEl('billing_start').hide();
			this.getEl('billing_stop').show();
			this.getEl('billing_start').click();
		} else {
			this.getEl('billing_stop').hide();
			this.getEl('billing_start').show();
			this.stopBillingTimer(true);
		}
	},

  getFormData: function(form) {
    var form = form || this.getEl('billing_form');
    var old = form.find('input, textarea, select').serializeArray();
    var data = {};
    for (var idx = 0; idx < old.length; idx++) {
      var entry = old[idx];
      if (entry.name.indexOf('billing_type') !== -1) {
        data.billing_type = entry.value;
      } else {
        data[entry.name] = entry.value;
      }
    }
    return data;
  },

  getBillingType: function() {
    var form = this.getEl('billing_form');
    var typeInputs = form.find('input[name=' + this.baseId + '_billing_type]');
    return typeInputs.is('[type="hidden"]')
      ? typeInputs.val()
      : typeInputs.filter(':checked').val();
  },

	initForm: function() {
		this.doInitForm();
	},

	addBillingRow: function(html) {
		var add = $(html);
		var billingRows = this.getEl('billing_rows');

		billingRows.append(add);
		add.find('.timeago').timeago();
		billingRows.closest('table').show();
	},

	updateBillingForm: function(reset) {
		var replyBaseId = $('form.ticket-reply-form', this.getEl('replybox_wrap')).data('base-id');
		if (replyBaseId) {
			var replyBillingRow = $('#' + replyBaseId + '_billing_reply');
		} else {
			var replyBillingRow = false;
		}

		this.clearTimer();

		if (this.getBillingType() === 'time') {
			if (this.getEl('billing_stop').is(':visible')) {
				// "stop" means it was running, so start it again
				this.startBillingTimer(reset);
			}

			if (replyBillingRow) {
				replyBillingRow.show();
				replyBillingRow.find('input[type=checkbox]').attr('disabled', false);
			}
		} else {
			this.stopBillingTimer(reset);

			if (replyBillingRow) {
				replyBillingRow.hide();
				replyBillingRow.find('input[type=checkbox]').attr('disabled', true);
			}
		}
	},

	clearTimer: function() {
		if (this.billingTimer) {
			clearInterval(this.billingTimer);
			this.billingTimer = null;
		}
	},

	resetBillingForm: function() {
		var form = this.getEl('billing_form'), self = this;

		$.get(form.data('refresh-url'), function (data) {
			form.replaceWith(data);

			self.timeout = setTimeout((function() {
				this.initForm();

				if (this.getBillingType() === 'time' && this.options.auto_start_bill) {
					this.startBillingTimer(true);
				} else {
					this.stopBillingTimer(true);
				}
			}).bind(self), 100);
		});
	},

	startBillingTimer: function(reset) {
		if (reset) {
			this.billingStart = new Date();
			this.billingExtraTime = 0;
		} else {
			if (this.billingStart) {
				this.billingExtraTime = Math.floor((new Date() - this.billingStart) / 1000) + this.billingExtraTime;
			}
			this.billingStart = new Date();
		}

		this.clearTimer();
		this.updateBillingTimer(true, true);

		var self = this;
		this.billingTimer = setInterval(function() { self.updateBillingTimer(); }, 1000);
	},

	stopBillingTimer: function(reset) {
		if (reset) {
			this.billingStart = false;
			this.billingExtraTime = 0;
		} else {
			if (this.billingStart) {
				this.billingExtraTime = Math.floor((new Date() - this.billingStart) / 1000) + this.billingExtraTime;
			}
			this.billingStart = false;
		}

		this.clearTimer();
		this.updateBillingTimer(true);
	},

	updateBillingTimer: function(force, showZero) {
		var seconds = 0;
		if (this.billingStart) {
			seconds = Math.floor((new Date() - this.billingStart) / 1000);
		}
		seconds += this.billingExtraTime;

		var rawSeconds = seconds,
			hours = 0,
			minutes = 0;

		var form = this.getEl('billing_form');
		var timeInputs = {
			hours: this.getEl('billing_hours'),
			minutes: this.getEl('billing_minutes'),
			seconds: this.getEl('billing_seconds')
		};

		if (seconds >= 3600) {
			hours = Math.floor(seconds / 3600);
			timeInputs.hours.val(hours);
			seconds -= hours * 3600;
		} else if (force) {
			timeInputs.hours.val('');
		}

		if (seconds >= 60) {
			minutes = Math.floor(seconds / 60);
			timeInputs.minutes.val(minutes);
			seconds -= minutes * 60;
		} else if (force) {
			timeInputs.minutes.val('');
		}

		if (seconds > 0 || minutes > 0 || hours > 0 || showZero) {
			timeInputs.seconds.val(seconds || 0);
		} else if (force) {
			timeInputs.seconds.val('');
		}

		var replyBaseId = $('form.ticket-reply-form', this.getEl('replybox_wrap')).data('base-id');
		if (replyBaseId) {
			var reply = $('#' + replyBaseId + '_billing_reply');
			if (reply.length) {
				reply.find('input[type=checkbox]').val(rawSeconds);

				var text = '';
				if (hours) {
					text += hours + (hours > 1 ? ' hours ' : ' hour ');
				}
				if (minutes) {
					text += minutes + (minutes > 1 ? ' minutes ' : ' minute ');
				}
				text += seconds + ' seconds';

				$('#' + replyBaseId + '_billing_reply_time').text(text);
			}
		}
	},

	getEl: function(id) {
		if (this.baseId) {
			id = this.baseId + '_' + id;
		}

		return $('#' + id);
	},

	populateForm: function(form, charge) {

		if (charge.amount) {
			form.find('#billing_hours_edit_' + charge.id).val('');
			form.find('#billing_minutes_edit_' + charge.id).val('');
			form.find('#billing_seconds_edit_' + charge.id).val('');

			form.find('#billing_amount_edit_' + charge.id).val(charge.amount);
		} else if(charge.charge_time) {
			form.find('#billing_amount_edit_' + charge.id).val('');

			var hours, minutes, seconds;

			var seconds = charge.charge_time;

			if (seconds >= 3600) {
				hours = Math.floor(seconds / 3600);
				seconds -= hours * 3600;
			}

			if (seconds >= 60) {
				minutes = Math.floor(seconds / 60);
				seconds -= minutes * 60;
			}

			form.find('#billing_hours_edit_' + charge.id).val(hours);
			form.find('#billing_minutes_edit_' + charge.id).val(minutes);
			form.find('#billing_seconds_edit_' + charge.id).val(seconds);
		}
	},

	destroy: function() {
    clearInterval(this.billingTimer);
    clearTimeout(this.timeout);
    this.doInitForm = null;
    this.options = null;
    this.destroyEvents();
	}
});
