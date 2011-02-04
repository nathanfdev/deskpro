Orb.createNamespace('DeskPRO.Widget');

DeskPRO.Widget.CampfireAnnounce = new Class({
	Extends: DeskPRO.Widget.Widget,

	initWidget: function() {
		var options = {};
		options.apiInfo = $.parseJSON($('input.api-info', this.widgetEl).val());
		options.ticketInfo = $.parseJSON($('input.ticket-info', this.widgetEl).val());
		options.personInfo = $.parseJSON($('input.person-info', this.widgetEl).val());
		this.setOptions(options);

		// Dont need them anymore
		$('input.api-info, input.ticket-info, input.person-info', this.widgetEl).remove();

		$('button.send-trigger:first', this.widgetEl).click(this._sendToCampfire.bind(this));
	},

	_sendToCampfire: function() {

		$('button.send-trigger:first', this.widgetEl).removeClass('green');

		var msg = this.options.personInfo.display_name + ' wants to share "' + this.options.ticketInfo.subject + '": ' + this.options.ticketInfo.viewUrl;

		var options = {
			url: 'https://' + this.options.apiInfo.accountName + '.campfirenow.com/room/' + this.options.apiInfo.roomId + '/speak.json',
			username: this.options.apiInfo.authToken,
			password: 'x',
			contentType: 'application/json',
			processData: false,
			cache: false,
			type: 'POST',
			data: '{"message": {"type":"TextMessage", "body":"' + msg.replace(/"/g, '\\"') + '"} }'
		};

		this.ajax(options);
	}
});