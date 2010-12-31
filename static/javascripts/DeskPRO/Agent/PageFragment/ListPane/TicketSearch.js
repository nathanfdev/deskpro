Orb.createNamespace('DeskPRO.Agent.PageFragment.ListPane');

DeskPRO.Agent.PageFragment.ListPane.TicketSearch = new Class({
	Extends: DeskPRO.Agent.PageFragment.ListPane.Basic,

	wrapper: null,
	overlay: null,

	initPage: function(el) {
		
		this.wrapper = $(el);
		
		$('table > tbody > tr', el).click(function() {
			DeskPRO_Window.runPageRouteFromElement(this);
		});
		
		this.initDisplayOptions();
		this.initInfiniteScroll();
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
	//# Display options
	//#########################################################################
	
	displayOptionsWrapper: null,
	displayOptionsOverlay: null,
	displayOptionsList: null,
	initDisplayOptions: function() {
		
		this.displayOptionsList = $('.display-options:first ul.sortable-list', this.wrapper);
		var overlay_wrapper = this.displayOptionsWrapper = $('.display-options:first', this.wrapper);

		this.displayOptionsOverlay = new DeskPRO.UI.Overlay({
			contentElement: overlay_wrapper,
			triggerElement: $('.display-options-trigger', this.wrapper),
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
		$('.list thead th', this.wrapper).each(function() {
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
			if (this.wrapper.scrollTop()+20 >= this._scrollInnerHeights() - $('#pane_list').height()) {
				this.nextSearchPage();
			}
		}).bind(this));
	},
	
	_scrollInnerHeights_cache: null,
	_scrollInnerHeights: function() {
		if (this._scrollInnerHeights_cache !== null) return this._scrollInnerHeights_cache;
		var h = 0;
		$('#pane_list').children(':visible').each(function() {
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
		
		var loading = $('.loading-more', this.wrapper);
		loading.detach().appendTo(this.wrapper); // make sure its at the bottom
		loading.show();
		
		var last_page = parseInt($('.page-set:last', this.wrapper).data('page'));
		
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
		$('.loading-more', this.wrapper).hide();
		
		if (!html || !html.length) {
			this.noMoreResults = true;
			var nomore = $('.no-more-results', this.wrapper);
			nomore.detach().appendTo(this.wrapper); // make sure its at the bottom
			nomore.show();
			return;
		}
		
		this._scrollInnerHeights_cache = null;
		
		var el = $(html);
		el.insertAfter($('.page-set:last', this.wrapper));
		
		$('table > tbody > tr', el).click(function() {
			DeskPRO_Window.runPageRouteFromElement(this);
		});
	}
});