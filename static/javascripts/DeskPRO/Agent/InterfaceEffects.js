Orb.createNamespace('DeskPRO.Agent');

DeskPRO.Agent.InterfaceEffects = new Orb.Class({
	Implements: [Orb.Util.Events],
	initialize: function() {

	},

	initPage: function() {
		// tiptip in header
		$('#header-top .right.box-header.actions .wrapper-top-bar a').tipTip({
			delay: 50
		});
	}
});