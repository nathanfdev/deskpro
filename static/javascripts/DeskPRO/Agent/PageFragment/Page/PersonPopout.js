Orb.createNamespace('DeskPRO.Agent.PageFragment.Page');
DeskPRO.Agent.PageFragment.Page.PersonPopout = new Class({
	
	Extends: DeskPRO.Agent.PageFragment.Page.Person,
	
	TYPENAME: 'person',
	
	initPage: function(el) {
		this.parent(el);
		
		var content_container = $('.content:first', this.popout);
		this.tabManager = new DeskPRO.Agent.TabManager(content_container);

		this.tabManager.addEvents({
			addTab: this._onTabAdd.bind(this),
			activateTab: this._onTabActivate.bind(this),
			deactivateTab: this._onTabDeactivate.bind(this),
			removeTab: this._onTabRemove.bind(this)
		});
	},
	
	_tabStripClick: function(event) {
		var el_click = $(event.target);

		if (el_click.parent().is('li.tab')) {
			var el = el_click.parent();
		} else {
			var el = el_click.parentsUntil('li.tab');
			if (el.length) {
				el = el.parent(); // jquery doesnt include the actual parent in parentsUntil
			}
		}
		
		// If its not a tab, we can just ignore the event
		if (!el.is('li.tab')) {
			return;
		}
		
		// If the clicked thing was the close button...
		if (el_click.parent().is('.close')) {
			this.tabManager.removeTab(el.data('tab-id'));
			return;
		}
		
		// Otherwise activate the tab
		this.tabManager.activateTab(el.data('tab-id'));
	},
	
	_onTabAdd: function(tabData) {
		tabData.btnId = Orb.getUniqueId('tab_');
		
		var html = '<li id="'+tabData.btnId+'" data-tab-id="'+tabData.id+'" class="tab';
			if (tabData.page.TYPENAME != 'basic') {
				html += ' icon icon-' + tabData.page.TYPENAME;
			}
			html += '" title="' + tabData.title + '">';
		
			html += '<div class="title"><span>'+tabData.title+'</span></div>';
			html += '<div class="close"><span /></div>';
		html += '</li>';
		
		var li = $(html);
		
		li.appendTo(this.tabStrip);
		this.resizeTabListWidth();
		
		// Add tooltip
		$(li).tipTip({defaultPosition: 'bottom'});
	},
	
	_onTabDeactivate: function(tabData, container, isActivating) {
		if (!isActivating) {
			var last_tab = $(':last', this.tabStrip);
			if (last_tab.length) {
				this.tabManager.activateTab(last_tab.data('tab-id'));
			}
		}
		$('#tiptip_holder').hide();
	},
	
	_onTabActivate: function(tabData) {
		$('li', this.tabStrip).removeClass('tab-active');
		$('#' + tabData.btnId, this.tabStrip).addClass('tab-active');
	},
	
	_onTabRemove: function(tabData) {
		$('#' + tabData.btnId).remove();
		
		$('#tiptip_holder').hide();
		
		this.resizeTabListWidth();
	}
});