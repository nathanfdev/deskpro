Orb.createNamespace('DeskPRO.Agent.ElementHandler');

DeskPRO.Agent.ElementHandler.OmniSearchSheet = new Orb.Class({
	Extends: DeskPRO.ElementHandler,

	init: function() {
		var self = this;

		$('#dp_omnibox_adv').on('click', function(ev) {
			ev.stopPropagation();
			ev.preventDefault();

			self.open();
		});

		this.el.on('click', function(ev) {
			ev.stopPropagation();
		});
	},

	_initSheet: function() {
		var self = this;

		if (this._hasInitSheet) return;
		this._hasInitSheet = true;

		this.typeTabs = new DeskPRO.UI.SimpleTabs({
			triggerElements: $('> header > ul > li', this.el),
			onBeforeTabSwitch: function(info) {
				var id = info.tabContent.attr('id');

				switch (id) {
					case '#dp_searchsheet_tickets': this._initTicketSearch(info.tabContent); break;
					case '#dp_searchsheet_people': this._initPeopleSearch(info.tabContent); break;
					case '#dp_searchsheet_orgs': this._initOrgsSearch(info.tabContent); break;
					case '#dp_searchsheet_content': this._initContentSearch(info.tabContent); break;
				}
			}
		});

		this.el.on('click', '.close-search-sheet', function() {
			self.close();
		});
	},

	updatePositions: function() {
		var left = $('#dp_list').offset().left - 13;
		var width = ($('#dp_content').offset().left + 13) - left;

		this.el.css({
			left: left,
			width: width
		});
	},

	open: function() {
		if (this._isOpen) return;

		this._initSheet();

		this._isOpen = true;
		this.updatePositions();

		this.el.slideDown('fast');
	},

	isOpen: function() {
		return this._isOpen;
	},

	close: function() {
		if (!this.isOpen()) {
			return false;
		}

		this.el.slideUp('fast');
		this._isOpen = false;
	},


	//##########################################################################
	//# Ticket Search
	//##########################################################################

	_initTicketSearch: function(tab) {
		if (this._hasInitTicketSearch) return;
		this._hasInitTicketSearch = true;

		$('ul.property-list > li > .values > select', tab).each(function() {
			var ob = new DeskPRO.UI.OptionBoxBuilder({
				values: $(this)
			});
		});
	},


	//##########################################################################
	//# People Search
	//##########################################################################

	_initPeopleSearch: function(tab) {
		if (this._hasInitPeopleSearch) return;
		this._hasInitPeopleSearch = true;
	},


	//##########################################################################
	//# Org Search
	//##########################################################################

	_initOrgsSearch: function(tab) {
		if (this._hasInitOrgsSearch) return;
		this._hasInitOrgsSearch = true;
	},


	//##########################################################################
	//# Content Search
	//##########################################################################

	_initContentSearch: function(tab) {
		if (this._hasInitContentSearch) return;
		this._hasInitContentSearch = true;
	}
});
