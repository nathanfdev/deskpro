Orb.createNamespace('DeskPRO.Agent.PageFragment.Page');

DeskPRO.Agent.PageFragment.Page.NewTicket = new Class({

	Extends: DeskPRO.Agent.PageFragment.Page.BasicTicket,

	TYPENAME: 'newticket',

	initPage: function(el) {

		this.parent(el);

		Orb.Compat.WebForms.placeholder($('input[name="subject"]', this.wrapper));
		Orb.Compat.WebForms.placeholder($('input.person-name-search', this.wrapper));

		// Reply box always open
		this.toggleReplyBar('on');

		this.ticketReplyTabs.children('li.close-trigger').hide();

		this._initNewUser();
		this._initUserChoice();
	},

	newUserHelper: null,
	_initNewUser: function() {
		$('a.new-person-trigger', this.contentWrapper).click(this._openNewUser.bind(this));
		this.newUserHelper = new DeskPRO.Agent.PageHelper.NewUserOverlay({
			context: this.contentWrapper,
			onAfterSave: this._handleNewUserSaved.bind(this),
			saveUrl: this.getMetaData('newUserUrl')
		});
	},

	_openNewUser: function(ev) {
		ev.preventDefault();
		this.newUserHelper.open();
	},

	_handleNewUserSaved: function(info) {
		var data = info.data;
		this.loadUser(data);
	},

	userSearchEl: null,
	_initUserChoice: function() {
		this.userSearchEl = $('input.person-name-search', this.wrapper);
		this.userSearchEl.autocomplete({
			minLenght: 2,
			source: this.getMetaData('userSearchUrl'),
			select: this.userSelected.bind(this)
		});
	},

	userSelected: function(ev, ui) {
		this.loadUser(ui.item)
	},

	loadUser: function(info) {
		$('input[name="person_id"]', this.contentWrapper).val(info.id);
		this.userSearchEl.val(info.label);
	},

	_saveCustomFields: function() {
		// Do nothing, we'll save custom fields when the ticket is saved
	},

	/**
	 * We're hijacking this event to submit the whole ticket at once
	 */
	_handleSendReply: function(els) {
		var data_els = $(':input', this.contentWrapper).add(els);
		var data = data_els.serializeArray();

		$.ajax({
			url: this.getMetaData('submitTicketUrl'),
			data: data,
			type: 'POST',
			dataType: 'json',
			success: this._handleTicketSubmit.bind(this)
		});
	},

	_handleTicketSubmit: function(data) {
		console.log(data);

		DeskPRO_Window.removePage(this);
		DeskPRO_Window.runPageRoute('ticket:' + data.loadUrl);
	}
});