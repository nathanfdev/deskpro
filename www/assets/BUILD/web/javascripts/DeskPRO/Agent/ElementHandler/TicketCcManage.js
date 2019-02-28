Orb.createNamespace('DeskPRO.Agent.ElementHandler');

DeskPRO.Agent.ElementHandler.TicketCcManage = new Orb.Class({
	Extends: DeskPRO.ElementHandler,

	initPage: function() {
		var self = this;
		var addUrl = this.el.data('add-url');
		this.deleteUrl = this.el.data('delete-url');

		var list = $('ul', this.el).first();
		var newrow = $('li.newrow', this.el);

		this.el.find('li').each(function() {
			self.initRow($(this));
		});

		var addRow = $('.addrow', this.el);
		if (addRow.length) {
			addRow.autoCompleteElement = new DeskPRO.Agent.ElementHandler.SimpleAutoComplete(addRow);

			addRow.on('click', '.cc-saverow-trigger', function(ev) {
				var btn = $(this);
				var email = $.trim($('input', addRow).val());

				if (!email) {
					return;
				}

				addRow.addClass('loading');

				$.ajax({
					url: addUrl,
					type: 'POST',
					data: { email_address: email },
					dataType: 'json',
					complete: function() {
						addRow.removeClass('loading');
					},
					success: function(data) {
						if (data.error) {
							if (data.error_code == 'invalid_email') {
								DeskPRO_Window.showAlert(btn.data('msg-invalid-email'));
							} else if (data.error_code == 'invalid_email_gatewayaccount') {
								DeskPRO_Window.showAlert(btn.data('msg-invalid-email-isaccount'));
							} else if (data.error_code == 'is_dupe') {
								DeskPRO_Window.showAlert(btn.data('msg-is-dupe'));
              } else if (data.error_code == 'cc_limit') {
                DeskPRO_Window.showAlert(btn.data('msg-cc-limit'));
							} else if (data.error_code == 'is_agent') {
								DeskPRO_Window.showAlert(btn.data('msg-invalid-email-isagent'));
								self.el.closest('.tabViewDetailContent').find('ul.cc-row-list').each(function() {
									$(this).empty().html(data.cc_list || '');
									$(this).find('li').each(function() {
										self.initRow($(this));
									});
								});
							}
							return;
						}

						if (data.is_dupe) {
							DeskPRO_Window.showAlert(btn.data('agent.tickets.participant_already_exists'));
							return;
						}

						addRow.find('input').val('');

						self.el.find('ul.cc-row-list').each(function() {
							$(this).empty().html(data.cc_list || '');
							$(this).find('li').each(function() {
								self.initRow($(this));
							});
						});
					}
				});
			});
		}
	},

	initRow: function(row) {
		var self = this;
		row.find('.remove-row-trigger').on('click', function(ev) {
			ev.stopPropagation();
			ev.preventDefault();

			var personId = row.data('person-id');
			var email = row.data('email-address');

			if (personId) {
				row.hide();
				$.ajax({
					url: self.deleteUrl,
					type: 'POST',
					data: { person_id: personId },
					dataType: 'json',
					success: function(data) {
						row.remove();
						self.el.closest('.tabViewDetailContent').find('ul.cc-row-list').each(function() {
							$(this).empty().html(data.cc_list || '');
							self.el.find('ul.cc-row-list').each(function() {
								$(this).empty().html(data.cc_list || '');
								$(this).find('li').each(function() {
									self.initRow($(this));
								});
							});
						});
					},
					error: function() {
						row.show();
					}
				});
			} else {
				row.remove();
			}
		});
	},

	destroy: function() {
		console.info();
		var addRow = $('.addrow', this.el);
		if (addRow.autoCompleteElement) {
      addRow.autoCompleteElement.destroy();
      addRow.autoCompleteElement = null;
		}

		this.destroyEl();
	}
});
