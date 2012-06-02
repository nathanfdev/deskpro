Orb.createNamespace('DeskPRO.Agent.PageFragment.ListPane');

DeskPRO.Agent.PageFragment.ListPane.KbList = new Orb.Class({
	Extends: DeskPRO.Agent.PageFragment.ListPane.Basic,

	initPage: function(el) {
		var self = this;
		this.wrapper = el;

		this.displayOptions = new DeskPRO.Agent.PageHelper.DisplayOptions(this, {
			prefId: 'kb-filter',
			resultId: this.meta.resultId,
			refreshUrl: this.meta.refreshUrl,
			prefSaveResultId: '0'
		});
		this.ownObject(this.displayOptions);

		this.selectionBar = new DeskPRO.Agent.PageHelper.SelectionBar(this, {});
		this.ownObject(this.selectionBar);

		this.listWrapper = $('section.kb-simple-list', this.wrapper);

		DeskPRO_Window.getTabWatcher().addTabTypeWatcher('ticket', this);
		this.addEvent('watchedTabActivated', function(tab) {
			if (DeskPRO_Window.getTabWatcher().getTabType(tab) == 'ticket') {
				self.initVisibleTicket();
			}
		});
		this.addEvent('watchedTabDeactivated', function(tab) {
			if (DeskPRO_Window.getTabWatcher().getTabType(tab) == 'ticket') {
				self.removeVisibleTicket();
			}
		});

		// Or if we're already viewing a tab ticket...c
		if (DeskPRO_Window.getTabWatcher().isTabTypeActive('ticket')) {
			self.initVisibleTicket();
		}

		$('section.kb-simple-list', this.wrapper)
			.on('click', '.kb-insert-link', function(ev) { ev.stopPropagation(); self.insertIntoTicket($(this).data('article-id'), 'link') })
			.on('click', '.kb-insert-content', function(ev) { ev.stopPropagation(); self.insertIntoTicket($(this).data('article-id'), 'content') })

		this.relatedContentList = new DeskPRO.Agent.PageHelper.RelatedContentList(this, {
			contentListEl: this.listWrapper
		});
		this.ownObject(this.relatedContentList);

		this.sendContentLink = new DeskPRO.Agent.PageHelper.SendContentLink(this, {
			contentListEl: this.listWrapper
		});
		this.ownObject(this.sendContentLink);

		// Sorting options
		var sortMenuBtn = $('.order-by-menu-trigger', this.wrapper).first();
		this.sortingMenu = new DeskPRO.UI.Menu({
			triggerElement: sortMenuBtn,
			menuElement: $('.order-by-menu', this.wrapper).first(),
			onItemClicked: function(info) {
				var item = $(info.itemEl);

				var prop = item.data('order-by')
				var label = item.text().trim();

				// Change the displayed label for some visual feedback
				$('.label', sortMenuBtn).text(label);

				var disOptWrap = self.displayOptions.getWrapperElement();
				var sel = $('select.sel-order-by', disOptWrap);
				$('option', sel).prop('selected', false);
				$('option.' + prop, sel).prop('selected', true);

				self.displayOptions.saveAndRefresh();
			}
		});
		this.ownObject(this.sortingMenu);

		this.enableHighlightOpenRows('article', 'article_id', 'article.article-');

		this.listNav = new DeskPRO.Agent.PageHelper.ListNav(this);

		this.massActions = new DeskPRO.Agent.PageHelper.MassActions(this, {
			isListView: false,
			applyAction: (this.massApplyAction).bind(this)
		});

		DeskPRO.ElementHandler_Exec($('#kb-mass-action-overlay'));
	},

	massApplyAction: function(wrapper, formData) {
		var data = formData,
			myFormData = $('input, select',wrapper).serializeArray();

		$(myFormData).each(function(index, param) {
			data[param.name] = param.value;
		});

		$.ajax({
			type: 'POST',
			url: BASE_URL + "agent/kb/article/ajax-mass-save",
			'data': data,
			'dataType': 'json',
			success: (this.actionAppliedCallback).bind(this)
		});
	},

	actionAppliedCallback: function(data) {
		if(data && data.success) {
			var category = data.category;

			var section = DeskPRO_Window.sections.publish_section;
			DeskPRO_Window.getSectionData('publish_section', function(data) {
				(section._initSection.bind(section))(data);
				$('#publish_outline_articlescat_list .kb-cat-' + category + ' .is-nav-item').click();
			});
		}

		if(data.error) {
			DeskPRO_Window.showAlert(data.error);
		}
	},

	initVisibleTicket: function() {
		this.listWrapper.addClass('with-visible-ticket');
	},

	removeVisibleTicket: function() {
		this.listWrapper.removeClass('with-visible-ticket');
	},

	insertIntoTicket: function(article_id, action) {

		var ticketTab = DeskPRO_Window.getTabWatcher().getActiveTabIfType('ticket');
		if (!ticketTab) {
			return;
		}

		var ticketPage = ticketTab.page;

		$.ajax({
			url: BASE_URL + 'agent/kb/article/'+article_id+'/info',
			type: 'GET',
			dataType: 'json',
			success: function(data) {
				if (action == 'content') {
					ticketPage.appendToMessage(data.content);
				} else {
					ticketPage.appendToMessage(data.permalink);
				}
			}
		});
	}
});
