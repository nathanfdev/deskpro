Orb.createNamespace('DeskPRO.Agent.PageFragment.ListPane');

DeskPRO.Agent.PageFragment.ListPane.TicketQueue = new Class({
	Extends: DeskPRO.Agent.PageFragment.ListPane.Basic,

	wrapper: null,
	contentWrapper: null,
	barWrapper: null,
	layout: null,
	overlay: null,

	initPage: function(el) {
		
		this.wrapper = $(el);
		this.contentWrapper = $('.content:first', this.wrapper);
		this.barWrapper = $('.actions-bar:first', this.wrapper);
		
		var center_id = Orb.getUniqueId('listpane_');
		var south_id = Orb.getUniqueId('listpane_');
		
		this.contentWrapper.attr('id', center_id);
		this.barWrapper.attr('id', south_id);
		
		this.layout = this.wrapper.layout({
			center: {
				paneSelector: '#' + this.contentWrapper.attr('id')
			},
			south: {
				paneSelector: '#' + this.barWrapper.attr('id'),
				size: 27,
				spacing_open: 0,
				spacing_closed: 0
			}
		});
		
		this.initDisplayOptions();
		this.initInfiniteScroll();
		this._initFlagMenu();
		
		this.actionsBarHelper = new DeskPRO.Agent.PageHelper.TicketActionsBar(this.wrapper, this.contentWrapper);
		this.actionsBarHelper.setActiveTable($('table.list:first', this.contentWrapper));
		
		this.initFeaturesOnCollection(el, {
			routes: ['table > tbody > tr .with-route'],
			times: ['abbr.timeago']
		});
	},
	
	destroyPage: function() {
		this.layout.panes.south.remove();
		this.layout.panes.south = false;
		this.layout.panes.center.remove();
		this.layout.panes.center = false;
		this.layout.destroy();
		this.layout = null;
		
		if (this.flagMenu) {
			this.flagMenu.destroy();
		}
	},
	
	activate: function() {
		if (this.getMetaData('queue_id')) {
			DeskPRO_Window.getMessageBroker().sendMessage('queue.view-activated', this.getMetaData('queue_id'));
		}
	},

	deactivate: function() {
		if (this.getMetaData('queue_id')) {
			DeskPRO_Window.getMessageBroker().sendMessage('queue.view-deactivated', this.getMetaData('queue_id'));
		}
	},

	//#########################################################################
	//# Flag menu
	//#########################################################################
	
	flagMenu: null,
	_initFlagMenu: function() {
		var self = this;
		this.flagMenu = new DeskPRO.UI.Menu({
			menuElement: $('> ul.ticket-flag-menu:first', this.contentWrapper),
			onItemClicked: function(info) {
				self._handleFlagMenuClick(info);
			}
		});
		
		$('table.list:first', this.contentWrapper).delegate('span.ticket-flag', 'click', function(ev) {
			self.flagMenu.openMenu(ev);
		});
	},
	
	_handleFlagMenuClick: function(info) {
		var item = $(info.itemEl);
		var flag = item.data('flag');

		var m = $(info.menu.getOpenTriggerElement());
		var ticketId = m.parent().parent().data('ticket-id');
		
		var old_flag = m.data('flag');
		
		m.removeClass('icon-flag-'+old_flag);
		m.addClass('icon-flag-'+flag);
		m.data('flag', flag);

		DeskPRO_Window.startLoadingIndicator();
		
		$.ajax({
			url: BASE_URL + 'agent/tickets/' + ticketId + '/ajax-save-flagged',
			type: 'POST',
			context: this,
			data: { color: flag },
			dataType: 'json',
			success: function(data) {
				this._handleFlagMenuClickSuccess(old_flag, flag);
			}
		});
	},
	
	_handleFlagMenuClickSuccess: function(el, old_flag, new_flag) {
		DeskPRO_Window.stopLoadingIndicator();
	},
	
	//#########################################################################
	//# Display options
	//#########################################################################
	
	displayOptionsWrapper: null,
	displayOptionsOverlay: null,
	displayOptionsList: null,
	initDisplayOptions: function() {
		
		this.displayOptionsList = $('.display-options:first ul.sortable-list', this.contentWrapper);
		var overlay_wrapper = this.displayOptionsWrapper = $('.display-options:first', this.contentWrapper);

		this.displayOptionsOverlay = new DeskPRO.UI.Overlay({
			contentElement: overlay_wrapper,
			triggerElement: $('.display-options-trigger', this.contentWrapper),
			onContentSet: function(eventData) {
				$('ul.sortable-list', eventData.wrapperEl).sortable({
					'axis': 'y'
				});
			}
		});
		
		$('.close-trigger', overlay_wrapper).click((function() {
			this.displayOptionsOverlay.closeOverlay();
		}).bind(this));
		
		$('.save-trigger', overlay_wrapper).click((function() {
			this.saveDisplayOptions();
		}).bind(this));
		
		// Set default checked values based on table
		var self = this;
		$('.list thead th', this.contentWrapper).each(function() {
			$('li[data-field="'+$(this).data('field')+'"] input[type="checkbox"]', self.displayOptionsList).attr('checked', true);
		});
	},
	
	saveDisplayOptions: function() {
		
		$('.buttons .loading-off', this.displayOptionsWrapper).hide();
		$('.buttons .loading-on', this.displayOptionsWrapper).show();
		
		var data = [];
		var pref_name = 'prefs[agent.ui.ticket-queues-display-fields.' + this.getMetaData('queue_id', 0) +'][]';
		
		$('input[type="checkbox"]:checked', this.displayOptionsList).each(function() {
			data.push({
				name: pref_name,
				value: $(this).attr('name')
			});
		});
		
		// We reload the same page which will have changes applied
		var url = this.getMetaData('routeUrl');
		var self = this;
		
		$.ajax({
			timeout: 20000,
			type: 'POST',
			url: BASE_URL + 'agent/misc/ajax-save-prefs',
			data: data,
			success: function() {
				
				$('.buttons .loading-off', this.displayOptionsWrapper).hide();
				$('.buttons .loading-on', this.displayOptionsWrapper).show();
				
				self.displayOptionsOverlay.closeOverlay();
				DeskPRO_Window.loadListPane(url);
			}
		});
	},
	
	
	//#########################################################################
	//# Infinite loading stuff
	//#########################################################################
	
	initInfiniteScroll: function() {
		this.wrapper.scroll((function() {
			if (this.contentWrapper.scrollTop()+50 >= this._scrollInnerHeights() - this.contentWrapper.height()) {
				this.nextSearchPage();
			}
		}).bind(this));
	},
	
	_scrollInnerHeights_cache: null,
	_scrollInnerHeights: function() {
		if (this._scrollInnerHeights_cache !== null) return this._scrollInnerHeights_cache;
		var h = 0;
		this.contentWrapper.children(':visible').each(function() {
			h += $(this).height();
		});

		this._scrollInnerHeights_cache = h;
		
		return h;
	},
	
	isLoadingNext: false,
	noMoreResults: false,
	nextSearchPage: function() {
		if (this.isLoadingNext|| this.noMoreResults) return;
		this.isLoadingNext = true;
		
		var loading = $('.loading-more', this.contentWrapper);
		loading.detach().appendTo(this.contentWrapper); // make sure its at the bottom
		loading.show();
		
		var last_page = parseInt($('.page-set:last', this.contentWrapper).data('page'));
		
		var url = this.getMetaData('pageUrl').replace('$page', last_page+1)
		
		$.ajax({
			cache: false,
			type: 'GET',
			url: url,
			context: this,
			dataType: 'html',
			success: function (data) {
				this._handleAjaxSuccess(data);
			}
		});
	},
	
	_handleAjaxSuccess: function(html) {
		
		this.isLoadingNext = false;
		$('.loading-more', this.contentWrapper).hide();
		
		if (!html || !html.length) {
			this.noMoreResults = true;
			var nomore = $('.no-more-results', this.contentWrapper);
			nomore.detach().appendTo(this.contentWrapper); // make sure its at the bottom
			nomore.show();
			return;
		}
		
		this._scrollInnerHeights_cache = null;
		
		var el = $(html);
		el.insertAfter($('.page-set:last', this.contentWrapper));

		this.initFeaturesOnCollection(el, {
			routes: ['tr .with-route'],
			times: ['abbr.timeago']
		});
	}
});