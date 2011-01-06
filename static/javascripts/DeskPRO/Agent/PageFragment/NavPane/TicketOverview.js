Orb.createNamespace('DeskPRO.Agent.PageFragment.NavPane');
DeskPRO.Agent.PageFragment.NavPane.TicketOverview = new Class({
	Extends: DeskPRO.Agent.PageFragment.NavPane.Basic,
	
	wrapper: null,

	initPage: function(el) {
		
		this.parent(el);		
		this.wrapper = el;
		
		$('.alt-nav li', el).click(function() {
			DeskPRO_Window.runPageRouteFromElement(this);
		});
		
		// Set up the poller
		//DeskPRO_Window.getPoller().addData(
		//	[{name: 'do[]', value: 'get-queue-counts'}],
		//	'queues.counts',
		//	{recurring: true, minDelay: 15000, minDelayAfterOne: true}
		//);
		
		// Set up listener
		//DeskPRO_Window.getMessageBroker().addMessageListener('ticket-overview.counts', this.updatequeueCounts.bind(this));
		//DeskPRO_Window.getMessageBroker().addMessageListener('ticket-overview.view-activated', this.highlightActiveQueue.bind(this));
		//DeskPRO_Window.getMessageBroker().addMessageListener('ticket-overview.view-deactivated', this.unhighlightActiveQueue.bind(this));

		this._initModeSwitching();
		this._initGroupingMenu();
		
		this.loadList();
	},
	
	//#################################################################
	//# Handles switching modes
	//#################################################################
	
	_initModeSwitching: function() {
		var self = this;
		$('.nav-list-wrapper > h4', this.wrapper).click(function() {
			self._switchToModeEl(this);
		});
	},
	
	_switchToModeEl: function(el) {
		el = $(el);
		
		$('.nav-list-wrapper', this.wrapper).removeClass('on');
		$('.nav-list-wrapper .list', this.wrapper).html('');
		
		el.parent().addClass('on');
		this.loadList();
	},
	
	
	//#################################################################
	//# Handles updating of the grouping fields
	//#################################################################
	
	groupMenuEl: null,
	groupEl1: null,
	groupEl2: null,
	groupEl2_yes: null,
	
	_initGroupingMenu: function() {
		
		this.groupMenuEl = $('.grouping-menu', this.wrapper);
		this.groupEl1 = $('.options:first .grouping1', this.wrapper);
		this.groupEl2 = $('.options:first .grouping2', this.wrapper);
		this.groupEl2_no = $('.options:first .no-subgroup', this.wrapper);
		this.groupEl2_yes = $('.options:first .with-subgroup', this.wrapper);
		
		var self = this;
		var menu = new DeskPRO.UI.Menu({
			triggerElement: $('.grouping-menu-trigger', this.wrapper),
			menuElement: this.groupMenuEl,
			onItemClicked: function(info) {
				self._handleGroupingChanged(info);
			},
			onBeforeMenuOpened: function(info) {
				$('li[data-groupby]', self.groupMenuEl).show();
				
				var event = info.menu.getOpenTriggerEvent();
				var triggerEl = $(event.target);
				
				if (triggerEl.is('.grouping1')) {
					$('li[data-groupby="none"]', self.groupMenuEl).hide();
				} else {
					var grouping1 = self.groupEl1.data('groupby');
					
					// Hide primary grouping form sub-grouping menu
					$('li[data-groupby="'+grouping1+'"]', self.groupMenuEl).hide();
				}
			}
		});
	},
	
	_handleGroupingChanged: function(info) {
		var grouping1 = this.groupEl1.data('groupby');
		var grouping2 = this.groupEl2.data('groupby');
		
		var event = info.menu.getOpenTriggerEvent();
		var triggerEl = $(event.target);
		var itemEl = $(info.itemEl);
		
		if (triggerEl.is('.grouping1')) {
			grouping1 = itemEl.data('groupby');
			if (grouping1 == grouping2) {
				grouping2 = '';
			}
		} else {
			grouping2 = itemEl.data('groupby');
		}
		
		if (!grouping1 || grouping1 == 'none') grouping1 = 'department';
		if (!grouping2 || grouping2 == 'none') grouping2 = '';

		this.updateGrouping(grouping1, grouping2);
		this.loadList();
	},
	
	//#################################################################
	//# Group loading
	//#################################################################
	
	/**
	 * This just updates the page to show proper texts etc for the particular groups
	 */
	updateGrouping: function(grouping1, grouping2) {
		var grouping1_menuItemEl = $('[data-groupby="'+grouping1+'"]', this.groupMenuEl);
		
		if (grouping2 && grouping2.length) {
			var grouping2_menuItemEl = $('[data-groupby="'+grouping2+'"]', this.groupMenuEl);;
		} else {
			grouping2 = false;
			var grouping2_menuItemEl = $();
		}
		
		// Update
		this.groupEl1.html(grouping1_menuItemEl.html()).data('groupby', grouping1);
		
		if (grouping2) {
			this.groupEl2.html(grouping2_menuItemEl.html()).data('groupby', grouping2);
			this.groupEl2_no.hide();
			this.groupEl2_yes.show();
		} else {
			this.groupEl2.html('').data('groupby', '');
			this.groupEl2_no.show();
			this.groupEl2_yes.hide();
		}
	},
	
	/**
	 * This loads the lists for the currently selected group and mode
	 */
	loadList: function() {
		
		DeskPRO_Window.startLoadingIndicator();
		
		var grouping1 = this.groupEl1.data('groupby');
		var grouping2 = this.groupEl2.data('groupby') || '';
		var mode = $('.nav-list-wrapper.on', this.wrapper).data('mode');
		
		var data = {
			group1: grouping1,
			group2: grouping2,
			mode: mode
		};
		
		// Send AJAX
		$.ajax({
			url: BASE_URL + 'agent/ticket-search/overview-nav',
			type: 'GET',
			data: data,
			context: this,
			dataType: 'html',
			success: function(html) {
				this._groupListLoaded(html);
			}
		});
	},
	
	_groupListLoaded: function(html) {
		
		DeskPRO_Window.stopLoadingIndicator();
		
		var list = $('.nav-list-wrapper.on .list', this.wrapper).html(html);
		var self = this;
		$('li', list).click(function() {
			self._groupItemClicked($(this));
		});
	},
	
	_groupItemClicked: function(li) {
		DeskPRO_Window.runPageRouteFromElement(li);
		$('.nav-list-wrapper.on .list li', this.wrapper).removeClass('on');
		li.addClass('on');
	}
});