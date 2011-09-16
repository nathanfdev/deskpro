Orb.createNamespace('DeskPRO.Agent.PageHelper');

DeskPRO.Agent.PageHelper.Results = new Orb.Class({
	Implements: [Orb.Util.Events, Orb.Util.Options],

	initialize: function(page, options) {
		var self = this;

		this.page = page;
		this.wrapper = page.wrapper;

		this.options = {
			/**
			 * The display options helper
			 * Defaults to page.displayOptions
			 * @option {DeskPRO.Agent.PageHelper.DisplayOptions}
			 */
			displayOptions: null,

			/**
			 * The container to append results to
			 * Defaults to 'wrapper .list-listing'
			 * @option {jQuery}
			 */
			resultsContainer: null,

			/**
			 * The row that contains the "more" button, "no more results" etc
			 * Defaults to 'wrapper footer.results-nav'
			 * @option {jQuery}
			 */
			navEl: null,

			/**
			 * The [xx] in "Showing [xx] of xx results" line
			 * Defaults to 'wrapper .results-showing-count'
			 * @option {jQuery}
			 */
			showingCountEl: null,

			/**
			 * The IDs of all the results
			 * @option {Array}
			 */
			resultIds: null,

			/**
			 * The wrapper around a result row item
			 * @option {String}
			 */
			resultRowSelector: 'article.row-item',

			/**
			 * How many results to show per page
			 * @option {Integer}
			 */
			perPage: 50
		};
		this.setOptions(options);

		this.displayOptions    = this.options.displayOptions || page.displayOptions;
		this.resultsContainer  = this.options.resultsContainer || $('.list-listing', this.wrapper);
		this.navEl             = this.options.navEl || $('footer.results-nav', this.wrapper);
		this.moreButton        = $('button.show-more', this.navEl);
		this.showingCountEl    = this.options.showingCountEl || $('.results-showing-count', this.wrapper);

		this.resultCount   = this.options.resultIds.length;

		// Chunk into pagesets
		this.resultPages = Orb.arrayChunk(this.options.resultIds, this.options.perPage);

		// could be a big array, we should release the old one if we can
		delete this.options.resultIds;

		this.numPages = this.resultPages.length;
		this.currentPage = 1;

		this.updateShowingCount();
		if (this.getCurrentPage() == this.getNumPages()) {
			this.showNoMore();
		}

		this.moreButton.click(this.loadNextPage.bind(this));
	},


	/**
	 * Get the current page number (1-based)
	 *
	 * @return {Integer}
	 */
	getCurrentPage: function() {
		return this.currentPage;
	},


	/**
	 * Get the total number of pages
	 *
	 * @return {Integer}
	 */
	getNumPages: function() {
		return this.numPages;
	},


	/**
	 * Get IDs for a page
	 *
	 * @param {Integer} pageNum
	 */
	getPageIds: function(pageNum) {
		pageNum--;

		if (!this.resultPages[pageNum]) {
			return [];
		}

		return this.resultPages[pageNum];
	},


	/**
	 * Load the next page in the results
	 */
	loadNextPage: function() {
		var nextPage = this.getCurrentPage() + 1;
		if (nextPage > this.getNumPages) {
			return;
		}

		return this.loadNewPage(nextPage);
	},

	/**
	 * Load a new page
	 *
	 * @param {Integer} pageNum
	 */
	loadNewPage: function(pageNum) {

		// Already running
		if (this.navEl.is('.loading')) {
			return;
		}

		var evData = {html: null}, html = null;

		this.fireEvent('loadResultPage', [evData]);

		if (evData.html !== null) {
			html = evData.html;
			this.appendNewResults(html);
		} else {
			this.showLoading();

			var data = {
				'result_ids[]': this.getPageIds(pageNum),
				'display_fields[]': this.displayOptions.getDisplayFields()
			};

			$.ajax({
				url: this.page.meta.fetchResultsUrl,
				data: data,
				type: 'GET',
				dataType: 'html',
				context: this,
				complete: function() {
					this.hideLoading();
				},
				success: function(html) {
					this.appendNewResults(html);
				}
			});
		}
	},


	/**
	 * Render new results to the page
	 *
	 * @param html
	 */
	appendNewResults: function(html) {
		var results = $(html), count;

		this.resultsContainer.append(results);
		this.fireEvent('appendNewResults', [results, this]);

		this.currentPage++;

		if (this.getNumPages() == this.currentPage) {
			this.showNoMore();
		}

		this.updateShowingCount();
	},


	/**
	 * Show the 'loading' message
	 */
	showLoading: function() {
		this.navEl.addClass('loading');
	},


	/**
	 * Remove the 'loading' message
	 */
	hideLoading: function() {
		this.navEl.removeClass('loading');
	},


	/**
	 * Show the 'no more results' element
	 */
	showNoMore: function() {
		this.navEl.removeClass('loading');
		this.navEl.addClass('no-more-results');
	},


	/**
	 * Update the showing xxx of xxx line by counting the rows currently displayed
	 */
	updateShowingCount: function() {
		this.showingCount = $(this.options.resultRowSelector, this.resultsContainer).length;
		this.showingCountEl.empty().text(this.showingCount);

		this.fireEvent('showingCountUpdated', [this.showingCount, this.showingCountEl, this]);
	},

	destroy: function() {
		this.options = null;
		this.resultPages = null;
	}
});
