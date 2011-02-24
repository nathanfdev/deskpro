Orb.createNamespace('DeskPRO.Agent');

DeskPRO.Agent.InterfaceEffects = new Class({
	Implements: [Events],
	initialize: function() {
	    
	},
	
	initPage: function() {
		// tiptip in header
		$('#header-top .right.box-header.actions .wrapper-top-bar a').tipTip({
			delay: 50
		});
	}
});