Orb.createNamespace('DeskPRO.User.ElementHandler');

DeskPRO.User.ElementHandler.IdeaView = new Orb.Class({

	Extends: DeskPRO.User.ElementHandler.ElementHandlerAbstract,

	init: function() {

		var self = this;

		this.votesUsed = this.el.data('votes-used');
		this.voteUrl = this.el.data('vote-url');

		this.menuEl = $('#idea_vote_menu_wrap');
		this.menuEl.detach().appendTo('body');

		this.menuEl.click(function(ev) {
			ev.stopPropagation();
		});

		$('.close-trigger', this.menuEl).click(function() {
			self.closeMenu();
		});

		this.btnEl = $('#submit_vote_trigger');
		this.btnEl.click(function(ev) {
			ev.preventDefault();
			ev.stopPropagation();
			self.openMenu();
		});

		$(document).click(function() {
			self.closeMenu();
		});

		$('li', this.menuEl).click(function(ev) {
			ev.stopPropagation();
			self.clickVoteOption($(this));
		});
	},

	/**
	 * Handle the click on a number
	 * 
	 * @param li
	 */
	clickVoteOption: function(li) {
		$('li', this.menuEl).removeClass('on');
		li.addClass('on');

		$.ajax({
			url: this.voteUrl,
			data: {'vote': li.data('num')},
			context: this,
			success: function() {
				this.votesUsed = li.data('num');
				console.log('success');
			}
		});
	},


	/**
	 * Opens the results menu.
	 */
	openMenu: function() {
		this.updateMenuDims();

		// Update the selectable numbers, remaining votes
		var numShows = IdeaVotesRemaining + IdeaVotesUsed;
		if (numShows > IdeaVotesMaxPerIdea) {
			numShows = IdeaVotesMaxPerIdea;
		}

		$('.votes-allowed', this.menuEl).html(numShows);

		$('li.num', this.menuEl).hide();
		for (var i = 1; i <= numShows; i++) {
			$('li.num-' + i, this.menuEl).show();
		}

		if (this.votesUsed) {
			$('li', this.menuEl).removeClass('on');
			$('li.num-' + this.votesUsed, this.menuEl).addClass('on');
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
		var pos = this.btnEl.offset();

		var top   = pos.top;
		var left  = pos.left;

		this.menuEl.css({
			top: top,
			left: left
		});
	}
});