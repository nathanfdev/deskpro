Orb.createNamespace('DeskPRO.Agent.WindowElement.Section');

DeskPRO.Agent.WindowElement.Section.Tickets = new Orb.Class({
	Extends: DeskPRO.Agent.WindowElement.Section.AbstractSection,

	init: function() {
		this.button = $('#tickets_section_btn');

		var sectionEl = $('<section id="tickets_section">ddsdsd</section>');
		this.setSectionElement(sectionEl);
	}
});