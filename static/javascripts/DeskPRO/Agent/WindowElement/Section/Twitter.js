Orb.createNamespace('DeskPRO.Agent.WindowElement.Section');

DeskPRO.Agent.WindowElement.Section.Twitter = new Orb.Class({
	Extends: DeskPRO.Agent.WindowElement.Section.AbstractSection,

	init: function() {
		this.buttonEl = $('#twitter_section');

		this.setSectionElement($('<section id="twitter_outline"></section>'));

		$.ajax({
			url: BASE_URL + 'agent/twitter/get-section-data.json',
			context: this,
			success: function(data) {
				this._initSection(data);
			}
		});
	},

	_initSection: function(data) {
		this.sectionEl.html(data.section_html);
	}
});