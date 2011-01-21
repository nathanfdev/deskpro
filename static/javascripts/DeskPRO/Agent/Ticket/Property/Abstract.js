Orb.createNamespace('DeskPRO.Agent.Ticket.Property');

/**
 * Something that can be fetched or set on the ticket page. These are
 * managers that handle UI changes and getting and setting new values.
 */
DeskPRO.Agent.Ticket.Property.Abstract = new Class({

	Implements: [Events, Options],
	
	options: {},
	ticketPage: null,

	/**
	 * @param {DeskPRO.Agent.PageFragment.Page.Ticket} ticketPage
	 * @param {Object} options
	 */
	initialize: function(ticketPage, options) {
		
		if (options) this.setOptions(options);
		
		this.ticketPage = ticketPage;

		this.init();
	},
	
	/**
	 * Name for the property
	 *
	 * @return {String}
	 */
	getName: function() {
		
	},
	
	
	
	/**
	 * Gets the currently set value
	 *
	 * @return mixed
	 */
	getValue: function() {
		// override
	},
	
	
	
	/**
	 * Sets a new value. Must also update the UI if needed.
	 *
	 * @param mixed value
	 */
	setValue: function(value) {
		// override
	},
	
	
	
	/**
	 * Get the UI element used to display the property.
	 */
	getInterfaceElement: function() {
		if (this._interfaceEl !== null) return this._interfaceEl;

		this._interfaceEl = this._getInterfaceElement();
		
		return this._interfaceEl;
	},
	
	_interfaceEl: null,
	_getInterfaceElement: function() {
		// override
	},
	
	
	/**
	 * When a property is updated automatically (not from a user action, like in the background),
	 * this pulse action is applied to highlight and fade slowly.
	 */
	pulseInterfaceElement: function() {
		this.getInterfaceElement().effect('highlight', 1200);
	},
	
	
	/**
	 * Highlight the UI element to bring attention to some change.
	 */
	highlightInterfaceElement: function() {
		this.getInterfaceElement().addClass('change-on');
	},
	
	
	
	/**
	 * Remove the UI highlight
	 */
	unhighlightInterfaceElement: function() {
		this.getInterfaceElement().removeClass('change-on');
	}
});