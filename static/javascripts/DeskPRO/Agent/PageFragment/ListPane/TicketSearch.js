Orb.createNamespace('DeskPRO.Agent.PageFragment.ListPane');

DeskPRO.Agent.PageFragment.ListPane.TicketSearch = new Class({
	Extends: DeskPRO.Agent.PageFragment.ListPane.Basic,

	wrapper: null,

	initPage: function(el) {
		
		this.wrapper = $(el);
		
		$('.search-results table > tbody > tr', el).click(function() {
			DeskPRO_Window.runPageRouteFromElement(this);
		});
		
		$(el).scroll((function() {
			if ($(el).scrollTop()+40 >= this._scrollInnerHeights() - $('#pane_list').height()) {
				this.nextSearchPage();
			}
		}).bind(this));
	},
	
	_scrollInnerHeights_cache: null,
	_scrollInnerHeights: function() {
		if (this._scrollInnerHeights_cache !== null) return this._scrollInnerHeights_cache;
		var h = 0;
		$('#pane_list').children().each(function() {
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
		el.appendTo(this.wrapper);
		
		$('table > tbody > tr', el).click(function() {
			DeskPRO_Window.runPageRouteFromElement(this);
		});
	}
});