Orb.createNamespace('DeskPRO.Agent.WindowElement.Section');

DeskPRO.Agent.WindowElement.Section.Test = new Orb.Class({
	Extends: DeskPRO.Agent.WindowElement.Section.AbstractSection,

	init: function() {
		this.buttonEl = $('#test_section');

		this.setSectionElement($('<section id="test_outline"></section>'));

		this._initSection();
	},

	_initSection: function() {
		this.setHasInitialLoaded();
		this.contentEl.html('<div>Test</div>');
	}
});
