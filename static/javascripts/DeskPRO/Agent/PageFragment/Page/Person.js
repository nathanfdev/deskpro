Orb.createNamespace('DeskPRO.Agent.PageFragment.Page');
DeskPRO.Agent.PageFragment.Page.Person = new Class({
	
	Extends: DeskPRO.Agent.PageFragment.Basic,
	
	hasSetupEmailDlg: false,
	email_display: null,
	email_dlg: null,

	initPage: function(el) {
		// Collapsible headings
		$('.section', el).each(function() {
			var sec = $(this);
			var title = $('h5', sec);
			var content = $('div:first', sec);

			title.click(function() {
				if (content.is(':visible')) {
					content.slideUp(function() { sec.addClass('closed'); });
					
				} else {
					sec.removeClass('closed');
					content.slideDown();
				}
			});
		});
		
		$('.tip', el).tipTip({defaultPosition: 'left'});
		
		// Name is editable
		var name = $('.main .header h1:first', el);
		if (!name.attr('id')) {
			name.attr('id', Orb.getUniqueId());
		}
		
		var editable = new DeskPRO.Form.InlineEdit({
			baseElement: el,
			ajax: {
				url: BASE_URL + 'tech/people/' + this.meta.person_id + '/ajax-save'
			}
		});
		
		// Email pops up the email dialog
		this.email_display = $('.main .header .email:first', el);
		this.email_dlg =  $('.email-edit-dlg', el).dialog({
			autoOpen: false,
			buttons: {
				'Save': this.saveEmails.bind(this),
				'Cancel': function() { $(this).dialog('close'); }
			},
			width: 450,
			height: 300,
			open: this.initEmailDlg.bind(this)
		});
		
		this.email_display.dblclick((function() {
			this.email_dlg.dialog('open');
		}).bind(this));
	},
	
	initEmailDlg: function() {
		if (this.hasSetupEmailDlg) return;
		this.hasSetupEmailDlg = true;
		
		$('ul.emails-list', this.email_dlg).click(function(ev) {
			var el = $(ev.target);
			var parent_li = el.parent();
			if (!parent_li.length) {
				return;
			}
			
			if (el.is('.delete')) {
				parent_li.addClass('delete');
				if (parent_li.is('.new')) {
					parent_li.remove();
				}
			} else if (el.is('.undelete')) {
				parent_li.removeClass('delete');
			} else if (el.is('.set-primary')) {
				$('ul.emails-list li.primary', this.email_dlg).removeClass('primary');
				parent_li.addClass('primary');
			}
		});
		
		// Add buttn
		$('.new-email-btn', this.email_dlg).click((function() {
			var email_address = $('.new-email-input', this.email_dlg).val().trim();
			
			var tpl = $('.emails-list li.tpl', this.email_dlg).clone();
			tpl.removeClass('tpl');
			tpl.attr('data-new-email', email_address);
			$('.email-address', tpl).html(email_address);
			
			$('.emails-list', this.email_dlg).append(tpl);
			
		}).bind(this));
	},
	
	saveEmails: function() {
		var del_ids = [];
		var new_emails = [];
		var primary_id = 0;
		
		$('ul.emails-list li', this.email_dlg).each((function(i, el) {
			var el = $(el);
			if (el.is('.tpl')) return;
			
			// Exists
			if (el.is('.exists')) {
				if (el.is('.delete')) {
					del_ids.push(el.data('email-id'));
				}
				// If an email was deleted and was set as primary, we'll sort it out in PHP
				if (el.is('.primary')) {
					primary_id = el.data('email-id');
				}
				
			// New
			} else {
				new_emails.push(el.data('new-email'));
				if (el.is('.primary')) {
					primary_id = el.data('new-email');
				}
			}
		}).bind(this));
		
		var data = [];
		var i = null;
		while (i = del_ids.pop()) {
			data.push({
				name: 'del_ids[]',
				value: i
			});
		}
		while (i = new_emails.pop()) {
			data.push({
				name: 'new_emails[]',
				value: i
			});
		}
		data.push({
			name: 'primary_id',
			value: primary_id
		});
		
		$.ajax({
			timeout: 20000,
			type: 'POST',
			url: BASE_URL + 'tech/people/' + this.meta.person_id + '/ajax-save-emails',
			data: data,
			success: this.handleEmailSave.bind(this)
		});
	},
	
	handleEmailSave: function(data) {
		$('ul.emails-list', this.email_dlg).empty().html(data.dlg_html);
	}
});