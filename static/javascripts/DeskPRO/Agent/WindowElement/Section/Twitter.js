Orb.createNamespace('DeskPRO.Agent.WindowElement.Section');

DeskPRO.Agent.WindowElement.Section.Twitter = new Orb.Class({
	Extends: DeskPRO.Agent.WindowElement.Section.AbstractSection,

	init: function() {
		this.buttonEl = $('#twitter_section');

		this.urlFragmentName = 'twitter';

		this.setSectionElement($('<section id="twitter_outline"></section>'));
		this.refresh();
	},

	refresh: function() {
		$.ajax({
			url: BASE_URL + 'agent/twitter/get-section-data.json',
			context: this,
			success: function(data) {
				this._initSection(data);
			}
		});
	},

	_initSection: function(data) {
		this.setHasInitialLoaded();
		this.contentEl.html(data.section_html);
	}
});
