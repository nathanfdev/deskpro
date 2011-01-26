Orb.createNamespace('DeskPRO.Agent.TicketList.Property');

/**
 * These standard options are simple values taken from a menu. Since
 * they're so similar, this single property class can handle all of them.
 */
DeskPRO.Agent.TicketList.Property.StandardOption = new Class({
	Extends: DeskPRO.Agent.TicketList.Property.Abstract,
	
	optionName: null,
	displayNames: null,
	
	init: function() {
		var valid_options = ['department_id', 'category_id', 'product_id', 'priority_id', 'status', 'agent_id', 'agent_team_id'];
		
		if (valid_options.indexOf(this.options.optionName) == -1) {
			throw 'invalidOptionName:'+this.options.optionName;
		}
		
		this.optionName = this.options.optionName;
		
		switch (this.optionName) {
			case 'department_id': this.displayNameType = 'department'; break;
			case 'category_id': this.displayNameType = 'category'; break;
			case 'product_id': this.displayNameType = 'product'; break;
			case 'priority_id': this.displayNameType = 'priority'; break;
			case 'status': this.displayNameType = 'status'; break;
			case 'agent_id': this.displayNameType = 'agent'; break;
			case 'agent_team_id': this.displayNameType = 'agent_team'; break;
		}
	},
	
	getValue: function() {
		return this.getInterfaceElement().data('prop-value');
	},
	
	getName: function() {
		return this.optionName;
	},
	
	setValue: function(value) {		
		if (value == "0") value = 0;

		this.getInterfaceElement().data('prop-value', value);

		if (value) {
			var displayName = value;
			if (this.displayNameType) {
				displayName = DeskPRO_Window.getDisplayName(this.displayNameType, value);
				if (!displayName) displayName = value;
			}
			
			this.getInterfaceElement().removeClass('no-value').html(displayName);
		} else {
			this.getInterfaceElement().addClass('no-value').html('none');
		}
	},
	
	_getInterfaceElement: function() {
		return $(this._buildSelector('.prop-val.' + this.optionName+':first'), this.ticketPage.actionsBarHelper.tableEl);
	}
});