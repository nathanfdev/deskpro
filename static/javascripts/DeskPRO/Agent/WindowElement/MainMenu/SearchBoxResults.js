Orb.createNamespace('DeskPRO.Agent.WindowElement.MainMenu');

DeskPRO.Agent.WindowElement.MainMenu.SearchBoxResults = new Class({
	Extends: DeskPRO.Agent.WindowElement.MainMenu.Abstract,

	menuHasResults: false,

	init: function () {
		var self = this;
		$('#window_search_box').focus(function(ev) {
			if (self.shouldShowMenu() && !$('#window_search_form .search-drop:first').is(':visible')) {
				self.options.mainMenuOpener.openMenu($('#window_search_form'), ev);
			}
		}).click(function(ev) {
			// stop propgation because the focus above will open
			// the menu, dont want to bubble click into the container
			// to re-close it again
			ev.stopPropagation();
		}).keypress(this.queryChanged.bind(this));
	},

	shouldShowMenu: function() {
		var check = $('#window_search_form .results, #window_search_loading');
		if (!check.is(':visible')) {
			return false;
		}

		return true;
	},

	queryChanged_timeout: null,
	queryChanged: function() {

		if (!$('#window_search_box').val().trim().length) {
			this.clearList();
			return;
		}

		$('#window_search_loading').show();
		if (!$('#window_search_form .search-drop:first').is(':visible')) {
			this.options.mainMenuOpener.openMenu($('#window_search_form'));
		}

		// If we already have a timeout going, we'll
		// let the time run out.
		// We dont reset it because we'd rather the user
		// see some results as they type, instead of only when they pause
		if (this.queryChanged_timeout) {
			return;
		}

		this.queryChanged_timeout = this.updateResults.delay(600, this);
	},

	updateResults: function() {

		if (this.queryChanged_timeout) {
			window.clearTimeout(this.queryChanged_timeout);
			this.queryChanged_timeout = null;
		}

		var val = $('#window_search_box').val().trim();

		if (!val.length) {
			this.clearList();
			return;
		}

		$.ajax({
			timeout: 8000,
			type: 'POST',
			url: BASE_URL + 'agent/people-search/search-quick',
			data: {term: val, format: 'simplelist', limit: 5},
			context: this,
			success: function(data) {
				$('#window_search_loading').hide();
				this._handleUserResults(data);
			}
		});
	},

	_handleUserResults: function(items) {
		var li = $(items);
		if (!$('> li', li).length) {
			li = $('<li>No people found</li>');
		}

		this.updateList('people', items);
	},

	updateList: function(type, items) {
		var wrap = $('#window_search_' + type);
		var list = $('> .results-list > ul', wrap);

		list.html(items);
		wrap.show();
	},

	clearList: function() {
		$('#window_search_form .results').hide();
		$('#window_search_form .results-list > ul').html('');

		if (this.queryChanged_timeout) {
			window.clearTimeout(this.queryChanged_timeout);
			this.queryChanged_timeout = null;
		}

		this.closeMenu();
	}
});