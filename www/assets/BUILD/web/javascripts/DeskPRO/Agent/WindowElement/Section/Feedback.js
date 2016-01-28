Orb.createNamespace('DeskPRO.Agent.WindowElement.Section');

DeskPRO.Agent.WindowElement.Section.Feedback = new Orb.Class({
	Extends: DeskPRO.Agent.WindowElement.Section.AbstractSection,

	init: function() {
		this.buttonEl = $('#feedback_section');
		var self = this;

		this.urlFragmentName = 'feedback';

		this.setSectionElement($('<section id="feedback_outline"></section>'));

		DeskPRO_Window.getSectionData('feedback_section', this._initSection.bind(this));

		DeskPRO_Window.getMessageBroker().addMessageListener('agent.ui.new-feedback', this.reload, this);
		DeskPRO_Window.getMessageBroker().addMessageListener('agent.ui.feedback-status-update', this.reload, this);

		window.setInterval(function() {
			self.reload();
		}, 420000);
	},

	reload: function() {
		DeskPRO_Window.getSectionData('feedback_section', this._initSection.bind(this));
	},

	_initSection: function(data) {

		this.setHasInitialLoaded();

		this.contentEl.html(data.section_html);

		this._initSectionSearch();

		var self = this;
		this.catTabs = new DeskPRO.UI.SimpleTabs({
			context: this.sectionEl,
			triggerElements: $('#feedback_outline_tabstrip li'),
			onTabSwitch: function(info) {

			}
		});

		this.recountBadge();

		this.fireEvent('sectionInit');
	},

	_initSectionSearch: function() {
		var searchPane = this.contentEl.find('.source-pane-search');
		if (searchPane[0]) {
			this.searchForm = new DeskPRO.Agent.SourcePane.SearchForm(searchPane);
		}
	},

	recountBadge: function() {
		var count = 0;
		count += parseInt($('#feedback_validating_count').text().trim()) || 0;
		count += parseInt($('#feedback_comments_validating_count').text().trim()) || 0;
		this.updateBadge(count);
	}
});
