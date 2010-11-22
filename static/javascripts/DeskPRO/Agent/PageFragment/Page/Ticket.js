Orb.createNamespace('DeskPRO.Agent.PageFragment.Page');
DeskPRO.Agent.PageFragment.Page.Ticket = new Class({
	
	Extends: DeskPRO.Agent.PageFragment.Basic,

	popout: null,
	popout_overview: null,
	
	isMouseOverPopout: false,
	
	initPage: function(el) {
		
		var self = this;
		$('.person-overview', el).mouseover(function(event) {
			self.isMouseOverPopout = true;
			self.openPopOut(el, event);
		}).mouseout(function() {
			self.isMouseOverPopout = false;
			self.closePopoutOnmouseout.delay(500, self);
		});
		
		
		this.popout = $('.person-popout', el);
		this.popout.mouseover(function() {
			self.isMouseOverPopout = true;
		}).mouseout(function() {
			self.isMouseOverPopout = false;
			self.closePopoutOnmouseout.delay(500, self);
		});
		this.popout.detach().appendTo('body');
		
		this.popout_overview = $('.person-overview-popout', el);
		this.popout_overview.mouseover(function() {
			self.isMouseOverPopout = true;
		}).mouseout(function() {
			self.isMouseOverPopout = false;
			self.closePopoutOnmouseout.delay(500, self);
		});
		this.popout_overview.detach().appendTo('body');
	},
	
	destroyPage: function(el) {
		this.popout.remove();
		this.popout_overview.remove();
		
		this.popout = null;
		this.popout_overview = null;
	},
	
	openPopOut: function(el, event) {
		var orig = $('.person-overview', el);
		var pos = orig.offset();
		
		this.popout.css({
			'position': 'absolute',
			'top': (pos.top - 30),
			'left': (pos.left - this.popout.outerWidth() + 1),
			'display': 'block',
			'z-index': 9999998
		});
		
		this.popout_overview.css({
			'position': 'absolute',
			'top': pos.top,
			'left': pos.left,
			'display': 'block',
			'width': orig.width(),
			'height': orig.height(),
			'z-index': 9999997
		});
	},
	
	closePopoutOnmouseout: function() {
		if (this.isMouseOverPopout) {
			return;
		}
		
		this.popout.hide();
		this.popout_overview.hide();
	}
});