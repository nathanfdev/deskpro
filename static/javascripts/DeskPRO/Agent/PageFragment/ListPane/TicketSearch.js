Orb.createNamespace('DeskPRO.Agent.PageFragment.ListPane');

DeskPRO.Agent.PageFragment.ListPane.TicketSearch = new Class({
	Extends: DeskPRO.Agent.PageFragment.ListPane.Basic,

	wrapper: null,
	contentWrapper: null,
	overlay: null,

	initPage: function(el) {
		
		this.wrapper = $(el);
		this.contentWrapper = $('.content:first', this.wrapper);
		
		$('table > tbody > tr > td .subject', el).click(function() {
			DeskPRO_Window.runPageRouteFromElement(this);
		});
		
		this.initDisplayOptions();
		this.initInfiniteScroll();
		
		this.initActionsBar();
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
	//# Actions bar
	//#########################################################################
	
	selectedActionData: null,
	
	initActionsBar: function() {
		var action_title = $('.actions-bar .action-title', this.wrapper);
		var menu = new DeskPRO.UI.Menu({
			triggerElement: $('.actions-bar .action-title', this.wrapper),
			menuElement: $('.actions-bar .action-menu', this.wrapper),
			onItemClicked: (function(info) {
				var itemEl = $(info.itemEl);
				
				this.selectedActionData = {
					op: itemEl.data('op')
				};
				
				if (itemEl.data('op') == 'macro') {
					this.selectedActionData['macro_id'] = itemEl.data('macro-id');
				} else if (itemEl.data('op') == 'status') {
					this.selectedActionData['status'] = itemEl.data('status');
				}
				
				action_title.html(itemEl.html());
			}).bind(this)
		});
		
		$('.actions-bar .action-perform', this.wrapper).click((function() {
			this.performMassAction();
		}).bind(this));
		
		var table = $('table.list', this.contentWrapper);
		var count_el = $('.actions-bar .counter .count', this.wrapper);
		
		var menu = new DeskPRO.UI.Menu({
			triggerElement: $('.actions-bar .counter', this.wrapper),
			menuElement: $('..actions-bar .selected-menu', this.wrapper),
			onItemClicked: function(info) {
				var itemEl = $(info.itemEl);
				
				if (itemEl.data('op') == 'none') {
					$('input[type="checkbox"].ticket', table).attr('checked', false);
				} else if (itemEl.data('op') == 'all') {
					$('input[type="checkbox"].ticket', table).attr('checked', true);
				} else if (itemEl.data('op') == 'invert') {
					$('input[type="checkbox"].ticket', table).each(function() {
						if ($(this).is(':checked')) {
							$(this).attr('checked', false);
						} else {
							$(this).attr('checked', true);
						}
					});
				}
				
				// Update count
				count_el.html($('input[type="checkbox"].ticket:checked', table).length);
			}
		});
		
		var actions_bar = $('.actions-bar', this.wrapper);
		$('input[type="checkbox"].ticket', this.contentWrapper).live('click', function() {
			var num =  parseInt(count_el.html());
			
			if ($(this).is(':checked')) {
				num++;
			} else {
				num--;
			}
			
			if (num < 0) num = 0;
			
			 count_el.html(num);
			
			// Make sure its visible
			actions_bar.slideDown('fast');
		});
	},
	
	performMassAction: function() {
		if (this.selectedActionData == null) {
			return;
		}
		
		var data = [];
		
		$('input[type="checkbox"].ticket:checked', this.contentWrapper).each(function() {
			data.push({
				name: 'ticket_ids[]',
				value: $(this).val()
			});
		});
		
		Object.each(this.selectedActionData, function(v,k) {
			data.push({
				name: k,
				value: v
			});
		});
		
		DeskPRO_Window.startLoadingIndicator();
		$.ajax({
			cache: false,
			type: 'POST',
			data: data,
			url: BASE_URL + 'agent/ticket-search/ajax-mass-actions',
			context: this,
			dataType: 'json',
			success: function (data) {
				this._handleMassActionsReply(data);
			}
		});
	},
	
	_handleMassActionsReply: function(data) {
		DeskPRO_Window.stopLoadingIndicator();
		console.debug(data);
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
		
		console.log(h);
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
		
		$('table > tbody > tr', el).click(function() {
			DeskPRO_Window.runPageRouteFromElement(this);
		});
	}
});