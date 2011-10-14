Orb.createNamespace('DeskPRO.Agent.WindowElement.Section');

DeskPRO.Agent.WindowElement.Section.People = new Orb.Class({
	Extends: DeskPRO.Agent.WindowElement.Section.AbstractSection,

	init: function() {
		this.buttonEl = $('#people_section');

		this.urlFragmentName = 'people';

		this.setSectionElement($('<section id="people_outline"></section>'));

		$.ajax({
			url: BASE_URL + 'agent/people/get-section-data.json',
			context: this,
			success: function(data) {
				this._initSection(data);
			}
		});
	},

	_initSection: function(data) {

		this.setHasInitialLoaded();

		this.contentEl.html(data.section_html);

		var self = this;
		this.peopleTabs = new DeskPRO.UI.SimpleTabs({
			context: this.sectionEl,
			triggerElements: $('#people_outline_tabstrip li'),
			onTabSwitch: function(info) {

			}
		});

		this.orgTabs = new DeskPRO.UI.SimpleTabs({
			context: this.sectionEl,
			triggerElements: $('#people_outline_org_tabstrip li'),
			onTabSwitch: function(info) {

			}
		});
	}
});
