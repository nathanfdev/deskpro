Orb.createNamespace('DeskPRO.Agent.WindowElement.Section');

DeskPRO.Agent.WindowElement.Section.People = new Orb.Class({
	Extends: DeskPRO.Agent.WindowElement.Section.AbstractSection,

	init: function() {
		this.buttonEl = $('#people_section');

		this.urlFragmentName = 'people';

		this.setSectionElement($('<section id="people_outline"></section>'));

		this.reload();
	},

	reload: function() {
		DeskPRO_Window.getSectionData('people_section', (function(data) {
			var wasLaoded = false;
			if (this.hasLoaded) {
				wasLoaded = true;

				this.peopleTabs.destroy();
				delete this.peopleTabs;

				this.orgTabs.destroy();
				delete this.orgTabs;
			}

			this._initSection(data);

			if (!wasLaoded) {
				this.fireEvent('sectionInit');
			}
		}).bind(this));
	},

	reloadLabels: function() {
		$.ajax({
			url: BASE_URL + 'agent/people/get-section-data/labels.json',
			context: this,
			success: function(data) {
				$('#people_outline_tagcloud').empty().html(data.people_label_cloud);
				$('#people_outline_taglist').empty().html(data.people_label_list);

				$('#people_outline_org_tagcloud').empty().html(data.org_label_cloud);
				$('#people_outline_org_taglist').empty().html(data.org_label_list);
			}
		});
	},

	_initSection: function(data) {
		this.setHasInitialLoaded();

		this.contentEl.empty().html(data.section_html);

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
