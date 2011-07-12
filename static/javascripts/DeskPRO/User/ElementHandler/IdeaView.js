Orb.createNamespace('DeskPRO.User.ElementHandler');

DeskPRO.User.ElementHandler.IdeaView = new Orb.Class({

	Extends: DeskPRO.User.ElementHandler.ElementHandlerAbstract,

	init: function() {

		var self = this;

		this.menuEl = $('#idea_vote_menu_wrap');
		this.menuEl.detach().appendTo('body');

		this.menuEl.click(function(ev) {
			ev.stopPropagation();
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
			$('li', self.menuEl).removeClass('on');
			$(this).addClass('on');
		});
	},


	/**
	 * Opens the results menu.
	 */
	openMenu: function() {
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
		var pos = this.btnEl.offset();

		var top   = pos.top;
		var left  = pos.left;

		this.menuEl.css({
			top: top,
			left: left
		});
	}
});