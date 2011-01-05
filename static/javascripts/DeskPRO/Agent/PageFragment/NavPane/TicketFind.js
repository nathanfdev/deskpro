Orb.createNamespace('DeskPRO.Agent.PageFragment.NavPane');
DeskPRO.Agent.PageFragment.NavPane.TicketFind = new Class({
	Extends: DeskPRO.Agent.PageFragment.NavPane.Basic,
	
	wrapper: null,
	
	cancelClickActivateQueue: false,
	initPage: function(el) {
		
		this.parent(el);
		
		this.wrapper = el;
		var self = this;
		
		$('.main-nav li', el).click(function() {
			if (self.cancelClickActivateQueue) {
				self.cancelClickActivateQueue = false;
				return;
			}
			DeskPRO_Window.runPageRouteFromElement(this);
		});
	}
});