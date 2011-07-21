Orb.createNamespace('DeskPRO.User.ElementHandler');

DeskPRO.User.ElementHandler.IdeaView = new Orb.Class({

	Extends: DeskPRO.User.ElementHandler.ElementHandlerAbstract,

	init: function() {
		var voteHelper = new DeskPRO.User.ElementHandler.Helper.IdeaVote();

		this.btnEl = $('#submit_vote_trigger');
		this.btnEl.click(function(ev) {
			ev.preventDefault();
			ev.stopPropagation();
			voteHelper.openMenu($(this));
		});
	}
});