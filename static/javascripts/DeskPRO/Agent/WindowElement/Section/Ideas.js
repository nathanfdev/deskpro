Orb.createNamespace('DeskPRO.Agent.WindowElement.Section');

DeskPRO.Agent.WindowElement.Section.Ideas = new Orb.Class({
	Extends: DeskPRO.Agent.WindowElement.Section.AbstractSection,

	init: function() {
		this.buttonEl = $('#ideas_section');

		this.urlFragmentName = 'ideas';

		this.setSectionElement($('<section id="ideas_outline"></section>'));

		DeskPRO_Window.getSectionData('ideas_section', this._initSection.bind(this));
	},

	_initSection: function(data) {

		this.setHasInitialLoaded();

		this.contentEl.html(data.section_html);

		var self = this;
		this.catTabs = new DeskPRO.UI.SimpleTabs({
			context: this.sectionEl,
			triggerElements: $('#ideas_outline_tabstrip li'),
			onTabSwitch: function(info) {

			}
		});

		this.fireEvent('sectionInit');
	}
});
