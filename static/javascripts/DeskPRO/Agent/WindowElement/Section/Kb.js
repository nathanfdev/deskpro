Orb.createNamespace('DeskPRO.Agent.WindowElement.Section');

DeskPRO.Agent.WindowElement.Section.Kb = new Orb.Class({
	Extends: DeskPRO.Agent.WindowElement.Section.AbstractSection,

	init: function() {
		this.buttonEl = $('#kb_section');

		this.setSectionElement($('<section id="kb_outline"></section>'));

		$.ajax({
			url: BASE_URL + 'agent/kb/get-section-data.json',
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
		this.catTabs = new DeskPRO.UI.SimpleTabs({
			context: this.sectionEl,
			triggerElements: $('#kb_outline_tabstrip li'),
			onTabSwitch: function(info) {

			}
		});

		this.contentEl.addClass('scroll-content').tinyscrollbar();

		if (this.isVisible()) {
			this._onShowLoadList();
		}
	}
});