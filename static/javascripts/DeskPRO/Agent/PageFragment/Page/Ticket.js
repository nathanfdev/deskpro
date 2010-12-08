Orb.createNamespace('DeskPRO.Agent.PageFragment.Page');
DeskPRO.Agent.PageFragment.Page.Ticket = new Class({
	
	Extends: DeskPRO.Agent.PageFragment.Basic,

	wrapper: null,
	popout: null,
	popout_overview: null,
	
	isMouseOverPopout: false,
	hasInitPopout: false,
	popoutPage: null,
	
	initPage: function(el) {
		
		this.wrapper = el;
		
		var self = this;
		$('.person-overview', el).mouseover(function(event) {
			self.isMouseOverPopout = true;
			self.openPopOut(event);
		}).mouseout(function(event) {
			self.isMouseOverPopout = false;
			self.closePopoutOnmouseout.delay(500, self);
		});
		
		$('.person-popout', el).click(function(event) {
			event.stopPropagation();
		});
		
		
		this.popout = $('.person-popout', el);
		this.popout.mouseover(function() {
			self.isMouseOverPopout = true;
		}).mouseout(function(event) {
			self.isMouseOverPopout = false;
			self.closePopoutOnmouseout.delay(500, self);
		});
		this.popout.detach().appendTo('body');
		this.destroyEls.push(this.popout);
		
		this.popout_overview = $('.person-overview-popout', el);
		this.popout_overview.mouseover(function() {
			self.isMouseOverPopout = true;
		}).mouseout(function() {
			self.isMouseOverPopout = false;
			self.closePopoutOnmouseout.delay(500, self);
		});
		this.popout_overview.detach().appendTo('body');
		this.destroyEls.push(this.popout_overview);
		
		$('.open-person', this.popout_overview).click(function() {
			DeskPRO_Window.runPageRouteFromElement(this);
		});
	},
	
	openPopOut: function(event) {
		var orig = $('.person-overview', this.wrapper);
		var pos = orig.offset();
		var wrapper_pos = this.wrapper.offset();
		
		// can use the left position of the element to roughly
		// determine how wide the columns are
		// so we want it to stretch as far as we can, minus some wriggle room
		var width = pos.left - 35;
		
		this.popout.css({
			'position': 'absolute',
			'display': 'block',
			'z-index': 999998,
			'width': width,
			'overflow': 'auto'
		});
		this.popout.css({
			'top': (wrapper_pos.top - 8),
			'left': (pos.left - this.popout.outerWidth() - 20),
			'bottom': 30
		});
		
		this.popout_overview.css({
			'position': 'absolute',
			'top': pos.top,
			'left': pos.left - 20,
			'display': 'block',
			'width': orig.width(),
			'height': orig.height(),
			'z-index': 999997
		});
		
		if (!this.hasInitPopout) {
			this.popoutPage = new DeskPRO.Agent.PageFragment.Page.Person();
			this.popoutPage.setMetaData({
				person_id: 1
			});
			
			this.popoutPage.initPage(this.popout);
			this.hasInitPopout = true;
		}
	},
	
	closePopoutOnmouseout: function() {
		if (this.isMouseOverPopout) {
			return;
		}
		
		this.popout.hide();
		this.popout_overview.hide();
	}
});