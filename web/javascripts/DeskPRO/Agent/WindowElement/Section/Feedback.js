Orb.createNamespace('DeskPRO.Agent.WindowElement.Section');

DeskPRO.Agent.WindowElement.Section.Feedback = new Orb.Class({
	Extends: DeskPRO.Agent.WindowElement.Section.AbstractSection,

	init: function() {
		this.buttonEl = $('#feedback_section');

		this.urlFragmentName = 'feedback';

		this.setSectionElement($('<section id="feedback_outline"></section>'));

		DeskPRO_Window.getSectionData('feedback_section', this._initSection.bind(this));

		DeskPRO_Window.getMessageBroker().addMessageListener('agent.ui.new-feedback', this.reload, this);
		DeskPRO_Window.getMessageBroker().addMessageListener('agent.ui.feedback-status-update', this.reload, this);

		this.currentNavSelection = null;
	},

	reload: function() {
		if (this.contentEl) {
			// ID'ing based off the ID of the counter, the actual nav row doesnt have any id
			var selectedNav = this.contentEl.find('.nav-selected').find('.list-counter');
			if (selectedNav[0]) {
				this.currentNavSelection = selectedNav.attr('id');
			}
		}
		DeskPRO_Window.getSectionData('feedback_section', this._initSection.bind(this));
	},

	_initSection: function(data) {

		this.setHasInitialLoaded();

		this.contentEl.html(data.section_html);

		var self = this;
		this.catTabs = new DeskPRO.UI.SimpleTabs({
			context: this.sectionEl,
			triggerElements: $('#feedback_outline_tabstrip li'),
			onTabSwitch: function(info) {

			}
		});

		this.recountBadge();

		if (this.currentNavSelection) {
			$('#' + this.currentNavSelection).closest('.is-nav-item').addClass('nav-selected');
		}
		this.currentNavSelection = null;

		this.fireEvent('sectionInit');
	},

	recountBadge: function() {
		var count = 0;
		count += parseInt($('#feedback_validating_count').text().trim()) || 0;
		count += parseInt($('#feedback_comments_validating_count').text().trim()) || 0;
		this.updateBadge(count);
	}
});
