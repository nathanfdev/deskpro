Orb.createNamespace('DeskPRO.Agent.PageFragment.Page');
DeskPRO.Agent.PageFragment.Page.Ticket = new Class({
	
	Extends: DeskPRO.Agent.PageFragment.Basic,
	
	TYPENAME: 'ticket',

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
			self.closePopoutOnmouseout.delay(10, self);
		});
		
		$('.person-popout', el).click(function(event) {
			event.stopPropagation();
		});
		
		
		this.popout = $('.person-popout', el);
		this.popout.mouseover(function() {
			self.isMouseOverPopout = true;
		}).mouseout(function(event) {
			self.isMouseOverPopout = false;
			self.closePopoutOnmouseout.delay(10, self);
		});
		this.popout.detach().appendTo('body');
		this.destroyEls.push(this.popout);
		
		this.popout_overview = $('.person-overview-popout', el);
		this.popout_overview.mouseover(function() {
			self.isMouseOverPopout = true;
		}).mouseout(function() {
			self.isMouseOverPopout = false;
			self.closePopoutOnmouseout.delay(10, self);
		});
		this.popout_overview.detach().appendTo('body');
		this.destroyEls.push(this.popout_overview);
		
		this.popout_overview_content = $('.person-overview-popout-content', el);
		this.popout_overview_content.detach().appendTo('body');
		this.popout_overview_content.mouseover(function() {
			self.isMouseOverPopout = true;
		}).mouseout(function() {
			self.isMouseOverPopout = false;
			self.closePopoutOnmouseout.delay(10, self);
		});
		this.destroyEls.push(this.popout_overview_content);
		
		this.popout_overview_content.click(function() {
			DeskPRO_Window.runPageRouteFromElement(this);
		});
		
		$('.agent-reply .placeholder textarea', this.wrapper).focus((function() {
			this.showEditor();
		}).bind(this));
	},
	
	showEditor: function() {
		if (!this.initEditor()) {
			$('.editor-loading', this.wrapper).show();
			return;
		}
		
		var wrapper = this.wrapper;
		$('.agent-reply .placeholder', wrapper).slideUp(function() {
			$('.agent-reply .reply-area', wrapper).slideDown();
			$('.editor-loading', wrapper).hide();
		});
	},
	
	hasInitEditor: false,
	initEditor: function() {
		if (this.hasInitEditor) return true;
		this.hasInitEditor = true;
		
		$('.editor-loading', wrapper).show();
		
		var self = this;
		var wrapper =  this.wrapper;
		var replyArea = $('.agent-reply .reply-area', this.wrapper);
		
		$('.btn-cancel', replyArea).click(function() {
			$('.agent-reply .reply-area', wrapper).slideUp(function() {
				$('.agent-reply .placeholder', wrapper).slideDown();
			});
		});
		
		$('.btn-submit', replyArea).click((function() {
			this.sendReply();
		}).bind(this));
		
	
		this.newReplyEditor = $('textarea', replyArea).tinymce({
			script_url: DP_TINYMCE_URL,
			theme : "advanced",
			theme_advanced_buttons1: "bold,italic,underline,|,bullist,numlist,|,outdent,indent,|,link,unlink,image,|,code,blockquote,hr,removeformat",
			theme_advanced_buttons2: "",
			theme_advanced_buttons3: "",
			theme_advanced_buttons4: "",
			theme_advanced_buttons5: "",
			theme_advanced_toolbar_location: "top",
			theme_advanced_toolbar_align : "left",
			theme_advanced_resizing: true,
			theme_advanced_resize_horizontal: false,
			theme_advanced_statusbar_location: 'bottom',
			theme_advanced_path: false,
			width : "98%",
			height: '130px',
			setup: function(ed) {
				ed.onInit.add(function(ed) {
					self.showEditor();
				});
			}
		});
		
		// the tinymce setup onInit will show the editor when its done
		return false;
	},
	
	sendReply: function() {
		var replyArea = $('.agent-reply .reply-area', this.wrapper);
		$('.buttons', replyArea).hide();
		$('.send-reply-load', replyArea).show();
		
		var data = {
			'message': this.newReplyEditor.html()
		};
		$.ajax({
			url: BASE_URL + 'agent/tickets/' + this.getMetaData('ticket_id') + '/ajax-save-reply',
			type: 'POST',
			context: this,
			data: data,
			dataType: 'html',
			success: function(html) {
				this._handleSendReplySuccess(html);
			}
		});
	},
	
	_handleSendReplySuccess: function(html) {
		var wrapper = this.wrapper;
		var replyArea = $('.agent-reply .reply-area', this.wrapper);
		var ed = this.newReplyEditor;
		$('.agent-reply .reply-area', wrapper).slideUp((function() {
			$('.agent-reply .placeholder', wrapper).slideDown();
			$('.buttons', replyArea).show();
			$('.send-reply-load', replyArea).hide();
			ed.html('');
			
			this.displayNewMessage(html);
		}).bind(this));
	},
	
	displayNewMessage: function(html) {
		var last_message = $('.messages > ul > li.message-item:first', this.wrapper);
		var new_message = $(html).hide();
		new_message.insertBefore(last_message).slideDown();
	},
	
	openPopOut: function(event) {
		var orig = $('.person-overview', this.wrapper);
		var pos = orig.offset();
		var wrapper_pos = this.wrapper.offset();
		
		// can use the left position of the element to roughly
		// determine how wide the columns are
		// so we want it to stretch as far as we can, minus some wriggle room
		var width = pos.left - 35;
		
		var show_popout = true;
		if (width < 400) {
			show_popout = false;
		}
		
		if (show_popout) {
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
		}
		
		this.popout_overview_content.css({
			'position': 'absolute',
			'top': pos.top - this.popout_overview_content.padding().top,
			'left': pos.left - this.popout_overview_content.padding().left,
			'display': 'block',
			'width': orig.width() + 25,
			'height': orig.height(),
			'z-index': 999996
		})
		
		this.popout_overview.css({
			'position': 'absolute',
			'top': pos.top - this.popout_overview.padding().top,
			'left': pos.left - this.popout_overview.padding().left - 20,
			'display': 'block',
			'width': orig.width() + 30,
			'height': orig.height(),
			'z-index': 999996
		});
		
		if (!this.hasInitPopout && show_popout) {
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
		this.popout_overview_content.hide();
	}
});