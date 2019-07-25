Orb.createNamespace('DeskPRO.Agent.WindowElement.Section');

DeskPRO.Agent.WindowElement.Section.Community = new Orb.Class({
	Extends: DeskPRO.Agent.WindowElement.Section.AbstractSection,

	init: function() {
		this.buttonEl = $('#community_section');
		var self = this;

		this.urlFragmentName = 'community';

		this.setSectionElement($('<section id="community_outline"></section>'));

		DeskPRO_Window.getSectionData('community_section', this._initSection.bind(this));

		DeskPRO_Window.getMessageBroker().addMessageListener('agent.ui.new-community-topic', this.reload, this);
		DeskPRO_Window.getMessageBroker().addMessageListener('agent.ui.community_topic-status-update', this.reload, this);
		DeskPRO_Window.getMessageBroker().addMessageListener('publish.validating.list-remove', function (info) {
			var el = $('article.' + info.typename + '-' + info.contentId);
			self.listPage.listRemove(el);
		});

		window.setInterval(function() {
			self.reload();
		}, 420000);
	},

	reload: function() {
		DeskPRO_Window.getSectionData('community_section', this._initSection.bind(this), {
			brand_id: $('#community_brand_id').val()
		});
	},

	_initSection: function(data) {

		this.setHasInitialLoaded();

		this.contentEl.html(data.section_html);

		this._initSectionSearch();

		var self = this;
		this.catTabs = new DeskPRO.UI.SimpleTabs({
			context: this.sectionEl,
			triggerElements: $('#community_outline_tabstrip li'),
			onTabSwitch: function(info) {

			}
		});

    $('select#community_brand_id').select2().on('change', function() {
      self.reload();
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
		count += parseInt($.trim($('#community_validating_count').text())) || 0;
		count += parseInt($.trim($('#community_comments_validating_count').text())) || 0;
		this.updateBadge(count);
	}

});
