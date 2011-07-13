Orb.createNamespace('DeskPRO.User.ElementHandler');

DeskPRO.User.ElementHandler.OmniSearch = new Orb.Class({

	Extends: DeskPRO.User.ElementHandler.ElementHandlerAbstract,

	init: function() {
		this.menuEl = $('#omni_search_menu_wrap');
		this.menuEl.detach().appendTo('body');

		var self = this;
		
		this.searchEl = $('#omni_search_txt');
		this.searchEl.keypress(this.searchQueryUpdated.bind(this));
		this.searchEl.focus(function() {
			self.openMenu(true);
		});
		$(document).click(function() {
			self.closeMenu();
		});

		this.searchEl.keypress(function() {
			self.updateSearch();
		});

		// When clicking the menu el itself, cancel bubble
		// so it doesnt get to document click and close
		this.menuEl.click(function(ev) { ev.stopPropagation(); });
		this.searchEl.click(function(ev) { ev.stopPropagation(); });

		this.resultsWrapper = $('.results', this.menuEl);

		// Make example terms clickable
		$('.example-query', this.el).click(function(ev) {

			ev.preventDefault();
			ev.stopPropagation();

			var text = $(this).text();
			self.setSearchQuery(text);

			self.searchEl.focus();
			self.doUpdateSearch();
		});
	},


	updateSearch: function() {

		var terms = this.searchEl.val().trim();

		if (!terms.length) {
			this.resultsWrapper.empty();
			this.closeMenu();

			if (this.updateTimer) {
				window.clearTimeout(this.updateTimer);
			}
		}

		if (this.updateTimer) {
			return;
		}

		this.updateTimer = this.doUpdateSearch.delay(250, this);
	},

	doUpdateSearch: function() {
		this.updateTimer = false;
		var terms = this.searchEl.val().trim();

		if (!terms) return;

		$.ajax({
			url: BASE_URL + 'search/omnisearch/' + encodeURI(terms),
			dataType: 'html',
			context: this,
			success: function(html) {
				var ul = $(html);

				this.resultsWrapper.empty().append(ul);
				this.openMenu(true);
			}
		});
	},

	
	/**
	 * Opens the results menu.
	 */
	openMenu: function(only_with_results) {

		if (only_with_results) {
			if (!$('> ul', this.resultsWrapper).length) {
				this.closeMenu();
				return false;
			}
		}

		this.updateMenuDims();

		this.menuEl.fadeIn('fast');
	},


	/**
	 * Close (hide) the results menu.
	 */
	closeMenu: function() {
		this.menuEl.fadeOut('fast');
	},


	/**
	 * Updates the menu element wrappers position and dimentions to make
	 * sure its under the search box all the itme
	 */
	updateMenuDims: function() {
		var pos = this.searchEl.offset();

		var top   = pos.top + this.searchEl.outerHeight();
		var left  = pos.left;
		var width = this.searchEl.outerWidth();

		this.menuEl.css({
			top: top,
			left: left,
			width: width
		});
	},

	/**
	 * Set the search query in the text box
	 *
	 * @param query
	 */
	setSearchQuery: function(query) {
		query = query.trim();

		this.searchEl.val(query);
		this.searchQueryUpdated();
	},


	searchQueryUpdated: function() {
		//TODO when search implemented
	}
});