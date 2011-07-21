Orb.createNamespace('DeskPRO.User.ElementHandler.Helper');

DeskPRO.User.ElementHandler.Helper.IdeaVote = new Orb.Class({

	_initMenu: function() {
		if (this.hasInit) return;
		this.hasInit = true;

		var self = this;

		this.menuEl = $('.idea_vote_menu_wrap:first');
		this.menuEl.detach().appendTo('body');

		this.menuEl.click(function(ev) {
			ev.stopPropagation();
		});

		$('.close-trigger', this.menuEl).click(function() {
			self.closeMenu();
		});

		$(document).click(function() {
			self.closeMenu();
		});

		$('li', this.menuEl).click(function(ev) {
			ev.stopPropagation();
			self.clickVoteOption($(this));
		});

		this.ideaEl = null;
	},

	/**
	 * Handle the click on a number
	 *
	 * @param li
	 */
	clickVoteOption: function(li) {
		$('li', this.menuEl).removeClass('on');
		li.addClass('on');

		var old_vote = parseInt(this.ideaEl.data('votes-used'));
		var new_vote = parseInt(li.data('num'));

		var diff = 0;
		diff = old_vote - new_vote;

		$('.votes-count', this.ideaEl).text(parseInt($('.votes-count', this.ideaEl).text()) - diff);

		this.ideaEl.data('votes-used', li.data('num'));

		$.ajax({
			url: this.ideaEl.data('vote-url'),
			data: {'vote': li.data('num')},
			context: this
		});
	},


	/**
	 * Opens the results menu.
	 */
	openMenu: function(ideaEl) {
		this._initMenu();
		this.ideaEl = ideaEl;
		
		this.updateMenuDims();

		// Update the selectable numbers, remaining votes
		var numShows = IdeaVotesRemaining + parseInt(this.ideaEl.data('votes-used'));
		if (numShows > IdeaVotesMaxPerIdea) {
			numShows = IdeaVotesMaxPerIdea;
		}

		$('.votes-allowed', this.menuEl).html(numShows);

		$('li.num', this.menuEl).hide();
		for (var i = 1; i <= numShows; i++) {
			$('li.num-' + i, this.menuEl).show();
		}

		if (this.ideaEl.data('votes-used')) {
			$('li', this.menuEl).removeClass('on');
			$('li.num-' + this.ideaEl.data('votes-used'), this.menuEl).addClass('on');
		} else {
			$('li.num-0', this.menuEl).addClass('on');
		}

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
		if (this.ideaEl.is('.idea-btn')) {
			var pos = this.ideaEl.offset();
		} else {
			var pos = $('.idea-btn', this.ideaEl).offset();
		}

		var top   = pos.top;
		var left  = pos.left;

		this.menuEl.css({
			top: top,
			left: left
		});
	}
});