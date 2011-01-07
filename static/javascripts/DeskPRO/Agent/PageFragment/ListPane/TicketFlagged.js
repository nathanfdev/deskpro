Orb.createNamespace('DeskPRO.Agent.PageFragment.ListPane');

DeskPRO.Agent.PageFragment.ListPane.TicketFlagged = new Class({
	Extends: DeskPRO.Agent.PageFragment.ListPane.Basic,

	wrapper: null,
	overlay: null,

	initPage: function(el) {
		
		this.wrapper = $(el);
		
		this.initFeaturesOnCollection(el, {
			routes: ['tr .with-route'],
			times: ['abbr.timeago']
		});
		
		$(el).scroll((function() {
			if ($(el).scrollTop()+20 >= this._scrollInnerHeights() - $('#pane_list').height()) {
				this.nextSearchPage();
			}
		}).bind(this));
	},
	
	activate: function() {
		if (this.getMetaData('flag')) {
			DeskPRO_Window.getMessageBroker().sendMessage('queue-flagged.view-activated', this.getMetaData('flag'));
		}
	},

	deactivate: function() {
		if (this.getMetaData('flag')) {
			DeskPRO_Window.getMessageBroker().sendMessage('queue-flagged.view-deactivated', this.getMetaData('flag'));
		}
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
		
		var nomore = $('.no-more-results', this.wrapper);
		if (!html || !html.length) {
			this.noMoreResults = true;
			nomore.detach().appendTo(this.wrapper); // make sure its at the bottom
			nomore.show();
			return;
		}
		nomore.hide();
		
		this._scrollInnerHeights_cache = null;
		
		var el = $(html);
		el.insertAfter($('.page-set:last', this.wrapper));

		this.initFeaturesOnCollection(el, {
			routes: ['tr .with-route'],
			times: ['abbr.timeago']
		});
	}
});