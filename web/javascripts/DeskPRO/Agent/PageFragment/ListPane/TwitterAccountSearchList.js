Orb.createNamespace('DeskPRO.Agent.PageFragment.ListPane');

DeskPRO.Agent.PageFragment.ListPane.TwitterAccountSearchList = new Orb.Class({
	Extends: DeskPRO.Agent.PageFragment.ListPane.Basic,

	initializeProperties: function() {
		this.parent();
		this.TYPENAME = 'twitter-search-list';
	},

	initPage: function(el) {
		this.el = el;

		var self = this;

		this.el.on('click', '.delete-icon', function(e) {
			e.preventDefault();
			e.stopPropagation();

			var $this = $(this);

			if (confirm($this.data('confirm'))) {
				$.ajax({
					url: $this.attr('href'),
					type: 'POST'
				});

				var row = $this.closest('.row-item');
				var container = row.closest('.content');

				row.remove();
				if (!container.find('.search-results article').length) {
					container.find('.list-listing.no-results').show();
				}

				// todo: update the window section
			}
		});

		this.searchBox = this.el.find('input.twitter-search');
		this.searchBox.on('keypress', function(e) {
			if (e.which != 13) {
				return true;
			}
			
			e.preventDefault();
			self.doSearch(self.searchBox.val());
		});
		this.el.find('.search-trigger').click(function(e) {
			e.preventDefault();
			self.doSearch(self.searchBox.val());
		});
	},
	
	doSearch: function(searchTerm) {
		searchTerm = $.trim(searchTerm);
		if (!searchTerm.length) {
			return;
		}

		var loader = this.el.find('.search-loading');

		loader.show();

		$.ajax({
			url: this.getMetaData('newSearchUrl'),
			data: {
				search_term: searchTerm
			},
			context: this,
			success: function(json) {
				DeskPRO_Window.runPageRoute('listpane:' + json.search_url);
				// todo: potentially need to update the window with the new search
			}
		}).always(function() {
			loader.hide();
		});
	}
});
