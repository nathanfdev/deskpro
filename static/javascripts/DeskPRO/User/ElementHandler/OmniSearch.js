Orb.createNamespace('DeskPRO.User.ElementHandler');

DeskPRO.User.ElementHandler.OmniSearch = new Orb.Class({

	Extends: DeskPRO.User.ElementHandler.ElementHandlerAbstract,

	init: function() {
		this.assistEl = $('#dp_search_assist');
		this.searchboxEl = $('#deskpro_search');

		this.searchboxEl.focus(this.activateAssist.bind(this));

		this.isActivated = false;
	},

	activateAssist: function() {
		this.isActivated = true;
		this.updatePosition();
		this.assistEl.show();
	},

	updatePosition: function() {
		var pos = this.searchboxEl.offset();
		var w = this.searchboxEl.outerWidth();
		var h = this.searchboxEl.outerHeight();

		this.assistEl.css({
			top: pos.top + h,
			left: pos.left - 1,
			width: w - 1
		});
	}
});
