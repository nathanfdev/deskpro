Orb.createNamespace('DeskPRO.Agent.WindowElement.Section');

DeskPRO.Agent.WindowElement.Section.Publish = new Orb.Class({
	Extends: DeskPRO.Agent.WindowElement.Section.AbstractSection,

	init: function() {
		this.buttonEl = $('#publish_section');

		this.setSectionElement($('<section id="publish_outline"></section>'));

		$.ajax({
			url: BASE_URL + 'agent/publish/get-section-data.json',
			context: this,
			success: function(data) {
				this._initSection(data);
			}
		});
	},

	_initSection: function(data) {

		this.setHasInitialLoaded();

		this.contentEl.html(data.section_html);
		//this.contentEl.addClass('scroll-content').tinyscrollbar();

		var self = this;
		this.typeTabs = new DeskPRO.UI.SimpleTabs({
			context: this.sectionEl,
			triggerElements: $('#portal_outline_tabstrip li')
		});
	}
});