Orb.createNamespace('DeskPRO.Agent.Notifier.Types');

DeskPRO.Agent.Notifier.Types.Abstract = new Class({
	Implements: [Events],


	sectionId: null, //override
	sectionEl: null,
	sectionList : null,

	initialize: function() {

		this.sectionEl = $('#' + this.sectionId);
		this.sectionList = $('> ul', this.sectionEl);

		this._initMessageListeners();
	},



	/**
	 * Hook method ot set up listeners on the message broker to handle
	 * things like adding or removing types of notifications.
	 */
	_initMessageListeners: function() {

	},



	/**
	 * The summary line goes right into the button
	 */
	getSummary: function() {
		// override
	},


	/**
	 * Update the section
	 */
	updateLines: function() {
		// override
	},



	/**
	 * Called when the list of items has been updated
	 */
	listUpdated: function() {
		this.fireEvent('listUpdated', this);
	}
});
