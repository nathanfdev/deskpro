Orb.createNamespace('DeskPRO.Agent.PageHelper');

DeskPRO.Agent.PageHelper.TicketBilling = new Orb.Class({
	Implements: [Orb.Util.Events, Orb.Util.Options],

	initialize: function(wrap, baseId, options) {
		this.baseId = baseId;
		this.options = {
			auto_start_bill: false
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

		var form = this.getEl('billing_form');
		var progress = this.getEl('billing_save_progress');
		var typeInputs = form.find('input[name=' + this.baseId + '_billing_type]');
		var billingRows = this.getEl('billing_rows');

		typeInputs.change(function() { self.updateBillingForm(true); });
		this.updateBillingForm(true);

		this.getEl('billing_stop').click(function() {
			self.stopBillingTimer(false);
			$(this).hide();
			self.getEl('billing_start').show();
		});
		this.getEl('billing_start').click(function() {
			if (self.getEl('billing_type_hidden').val() != 'time') {
				return;
			}

			self.startBillingTimer(false);
			$(this).hide();
			self.getEl('billing_stop').show();
		});
		this.getEl('billing_reset').click(function() {
			if (typeInputs.filter(':checked').val() == 'time') {
				if (self.getEl('billing_stop').is(':visible')) {
					self.startBillingTimer(true);
				} else {
					self.stopBillingTimer(true);
				}
			} else {
				self.stopBillingTimer(true);
			}
		});

		this.getEl('billing_save').click(function() {
			progress.show();

			$.ajax({
				url: $(this).data('submit-url'),
				data: form.find('input, textarea, select').serialize(),
				type: 'POST',
				dataType: 'json'
			}).done(function(json) {
				if (json.inserted && self.addBillingRow) {
					self.addBillingRow(json.html);
					self.resetBillingForm();
				}
			}).always(function() {
				progress.hide();
			});
		});

		wrap.on('click', 'a.billing-delete', function(e) {
			var $this = $(this);

			e.preventDefault();

			if (confirm(billingRows.data('delete-confirm'))) {
				$.ajax({
					url: $this.attr('href'),
					type: 'POST',
					dataType: 'json'
				}).done(function (json) {
					if (json.success) {
						var table = $this.closest('table');
						$this.closest('tr').remove();
						if (!table.find('tbody tr').length)
						{
							table.hide();
						}
					}
				});
			}
		});
		
		wrap.on('click', 'a.billing-edit', function(e) {
			e.preventDefault();
			
			var $this = $(this);
			
			var row = $this.parents('tr');
			
			billingRows.children('tr').removeClass('edit-mode');
			
			row.addClass('edit-mode');
			
			var charge = row.data('charge');
			
			self.populateForm(row, charge);
			
			return false;
		});
		
		wrap.on('click', 'a.billing-edit-discard', function(e) {
			e.preventDefault();
			
			var $this = $(this);
			
			$this.parents('tr').removeClass('edit-mode');
			
			return false;
		});
		
		wrap.on('click', 'a.billing-edit-save', function(e) {
			e.preventDefault();
			
			var $this = $(this);
			
			var row = $this.parents('tr');
			
			$.ajax({
				url: $this.attr('href'),
				data: row.find('input').serialize(),
				type: 'POST',
				dataType: 'json'
			}).done(function(json) {
				if (json.updated && json.html) {
					row.replaceWith(json.html);
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

	addBillingRow: function(html) {
		var add = $(html);
		var billingRows = this.getEl('billing_rows');

		billingRows.append(add);
		add.find('.timeago').timeago();
		billingRows.closest('table').show();
	},

	updateBillingForm: function(reset) {
		var form = this.getEl('billing_form');
		var typeInputs = form.find('input[name=' + this.baseId + '_billing_type]');

		if (typeInputs.is('[type="hidden"]')) {
			var val = typeInputs.val();
		} else {
			var val = typeInputs.filter(':checked').val();
		}

		this.getEl('billing_type_hidden').val(val);

		var replyBaseId = $('form.ticket-reply-form', this.getEl('replybox_wrap')).data('base-id');
		if (replyBaseId) {
			var replyBillingRow = $('#' + replyBaseId + '_billing_reply');
		} else {
			var replyBillingRow = false;
		}

		this.clearTimer();

		if (val == 'time') {
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
		var form = this.getEl('billing_form');

		this.getEl('billing_amount').val('');
		this.getEl('billing_comment').val('');

		if (this.getEl('billing_type_hidden').val() == 'time' && this.options.auto_start_bill) {
			this.startBillingTimer(true);
		} else {
			this.stopBillingTimer(true);
		}
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
		form.find('#billing_comment_edit_' + charge.id).val(charge.comment);
		
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
	}
});