Orb.createNamespace('DeskPRO.Agent.Ticket.Property');

/**
 * These standard options are simple values taken from a menu. Since
 * they're so similar, this single property class can handle all of them.
 */
DeskPRO.Agent.Ticket.Property.StandardOption = new Class({
	Extends: DeskPRO.Agent.Ticket.Property.Abstract,
	
	optionName: null,
	menuRepository: null,
	
	init: function() {
		var valid_options = ['department_id', 'category_id', 'product_id', 'priority_id', 'status', 'agent_id', 'agent_team_id'];
		
		if (valid_options.indexOf(this.options.optionName) == -1) {
			throw 'invalidOptionName:'+this.options.optionName;
		}
		
		this.optionName = this.options.optionName;
		
		this.menuRepository = this.ticketPage.ticketOptionsMenus[this.optionName];
	},
	
	
	getName: function() {
		return this.optionName;
	},
	
	getValue: function() {
		return this.getFormEl().val();
	},
	
	setValue: function(value) {
		this.getFormEl().val(value);
		
		if (value == "0") value = 0;

		if (value) {
			var menuEl = $('li[data-option-id="'+value+'"]:first', this.menuRepository.getListElement());
			var displayName = value;
			if (menuEl.length) {
				displayName = menuEl.html();
			}
			
			this.getInterfaceElement().removeClass('no-value').html(displayName);
		} else {
			this.getInterfaceElement().addClass('no-value').html(this.getDisplayEl().data('no-value'));
		}
	},
	
	_getInterfaceElement: function() {
		return $('.prop-val.'+this.optionName+':first', this.ticketPage.contentWrapper);
	},
	
	_formEl: null,
	getFormEl: function() {
		if (this._formEl !== null) return this._formEl;
		
		this._formEl = $('input.'+this.optionName+':first', this.ticketPage.valueForm);
		
		return this._formEl;
	}
});