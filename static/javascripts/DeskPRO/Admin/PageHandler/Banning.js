Orb.createNamespace('DeskPRO.Admin.PageHandler');

DeskPRO.Admin.PageHandler.Banning = new Class({

	Extends: DeskPRO.Admin.PageHandler.Basic,

	initPage: function() {
		var self = this;

		this.newIpOverlay = new DeskPRO.UI.Overlay({
			triggerElement: $('#new_ip_ban_btn'),
			contentElement: $('#ip_ban_overlay'),
			onBeforeOverlayOpened: function() {
				$('#ip_ban_overlay input[name="ip"]').val('');
			}
		});
		$('#ip_ban_overlay button.save-trigger').on('click', this.doNewIp.bind(this));

		this.newEmailOverlay = new DeskPRO.UI.Overlay({
			triggerElement: $('#new_email_ban_btn'),
			contentElement: $('#email_ban_overlay'),
			onBeforeOverlayOpened: function() {
				$('#email_ban_overlay input[name="email"]').val('');
			}
		});
		$('#email_ban_overlay button.save-trigger').on('click', this.doNewEmail.bind(this));

		$('#ip_rows').on('click', 'a.delete-trigger', function(ev) {
			ev.preventDefault();
			self.doDeleteIp($(this).parent().parent());
		});
		$('#email_rows').on('click', 'a.delete-trigger', function(ev) {
			ev.preventDefault();
			self.doDeleteEmail($(this).parent().parent());
		});
	},

	doNewIp: function() {
		var url = $('#ip_ban_overlay .save-url').val();
		url = url.replace('{ip}', $('#ip_ban_overlay input[name="ip"]').val());

		this.newIpOverlay.closeOverlay();

		$.ajax({
			url: url,
			type: 'POST',
			context: this,
			dataType: 'html',
			success: function(html) {
				$('#ip_rows').prepend(html);
			}
		});
	},

	doNewEmail: function() {

		var url = $('#email_ban_overlay .save-url').val();
		url = url.replace('{email}', $('#email_ban_overlay input[name="email"]').val());

		this.newEmailOverlay.closeOverlay();

		$.ajax({
			url: url,
			type: 'POST',
			context: this,
			dataType: 'html',
			success: function(html) {
				$('#email_rows').prepend(html);
			}
		});
	},

	doDeleteIp: function(row) {
		var url = $('a.delete-trigger:first', row).attr('href');

		$.ajax({
			url: url,
			type: 'POST',
			context: this,
			dataType: 'json',
			success: function(data) {
				row.remove();
			}
		});
	},

	doDeleteEmail: function(row) {
		var url = $('a.delete-trigger:first', row).attr('href');

		$.ajax({
			url: url,
			type: 'POST',
			context: this,
			dataType: 'json',
			success: function(data) {
				row.remove();
			}
		});
	}
});
