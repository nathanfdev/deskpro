Orb.createNamespace('DeskPRO.Agent.PageFragment.Page');

DeskPRO.Agent.PageFragment.Page.NewTicket = new Class({
	
	Extends: DeskPRO.Agent.PageFragment.Page.BasicTicket,
	
	TYPENAME: 'newticket',
	
	initPage: function(el) {

		this.parent(el);

		Orb.Compat.WebForms.placeholder($('input[name="ticket\[subject\]"]', this.wrapper));
		Orb.Compat.WebForms.placeholder($('input.person-name-search', this.wrapper));
		
		// Reply box always open
		this.toggleReplyBar('on');

		this.ticketReplyTabs.children('li.close-trigger').hide();
		
		this._initUserChoice();
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
		console.log(ui);
	},
	
	loadUser: function(user_id) {
		$('input[name="ticket\[person_id\]"]', this.contentWrapper);
	},
	
	_handleTicketOptionSave: function(option, optionId) {
		if (option == 'status') {
			var inputSel = 'input[name="ticket\[status\]"]';
		} else {
			var inputSel = 'input[name="ticket\['+option+'_id\]"]';
		}
		
		console.log(inputSel);
		
		var el = $(inputSel, this.contentWrapper);
		el.val(optionId);
	},
	
	_saveCustomFields: function() {
		// Do nothing, we'll save custom fields when the ticket is saved
	},
	
	_handleSendReply: function() {
		// Do nothing, we'll save the reply when ticket is saved
	}
});