Orb.createNamespace('DeskPRO.Agent.PageFragment.Page');
DeskPRO.Agent.PageFragment.Page.Person = new Class({

	Extends: DeskPRO.Agent.PageFragment.Basic,

	TYPENAME: 'person',

	wrapper: null,
	hasSetupEmailDlg: false,
	email_display: null,
	email_dlg: null,

	contactSection: null,

	notesSection: null,

	initPage: function(el) {

		this.wrapper = el;
		this.contentWrapper = $('div.layout-content:first', el);

		this.zIndex = 999999;

		var self = this;

		var cw = this.contentWrapper;
		cw.tinyscrollbar();
		$('div.scroll-content:first, div.scroll-viewport:first', this.contentWrapper).resize(function() {
			// When size changes within the pane, need to re-size the scroll
			cw.tinyscrollbar_update();
		});

		this.initRoutesOnCollection($('.with-route', this.wrapper));
		this.initTimesOnCollection($('time.timeago', this.wrapper));

		$('input[placeholder]', this.wrapper).each(function() {
			Orb.Compat.WebForms.placeholder(this);
		})

		// Name is editable
		var name = $('h3.name.editable:first', el);
		if (!name.attr('id')) {
			name.attr('id', Orb.getUniqueId());
		}

		var editable = new DeskPRO.Form.InlineEdit({
			baseElement: el,
			ajax: {
				url: BASE_URL + 'agent/people/' + this.meta.person_id + '/ajax-save'
			}
		});

		// Attach click to wrapper because
		// this same code is used on popout on ticket,
		// and clicks dont bubble to document click
		$(this.wrapper).click(function (ev) {
			editable.handleDocumentClick(ev);
		});

		// Email pops up the email dialog
		this.initEmailDlg();
		this.email_display = $('.main .header .email:first', el);

		//edit-contact-info
		this.editContactInfoMenu = new DeskPRO.UI.Menu({
			triggerElement: $('.edit-contact-info:first', this.wrapper),
			menuElement: $('.contact-info-edit-menu:first', this.wrapper),
			onItemClicked: function(info) {
				var type = $(info.itemEl).data('edit-type');
				if (type == 'email') {
					self.showEmailEditor();
				} else {
					self.startContactAdd(type);
				}
			}
		});

		// The main tabs at the bottom of the page
		var simpleTabs = new DeskPRO.UI.SimpleTabs({
			context: $('.full-container-tabbed', this.wrapper),
			triggerElements: $('.full-container-tabbed-tabs li', this.wrapper)
		});
		
		this.initNoteFormEditable();
		this.initNotePagination();
		this.initOrgEditable();
		this._initLabels();
		this._initCustomFieldsEditor();
	},

	destroyPage: function() {
		if (this.org_dlg) {
			this.org_dlg.remove();
		}

		if (this.email_dlg) {
			this.email_dlg.remove();
		}
	},

	updateCounts: function() {
		var wrap = $('.full-container-tabbed-tabs', this.wrapper);

		$.ajax({
			url: this.getMetaData('getUpdatedCountsUrl'),
			type: 'GET',
			context: this,
			dataType: 'json',
			success: function(counts) {
				Object.each(counts, function(v,k) {
					var sel = '.person-' + k + '-count';
					$(sel, wrap).html('(' + v + ')');
				});
			}
		});
	},

	//#########################################################################
	//# Custom fields
	//#########################################################################

	custom_fields_display: null,
	custom_fields_edit: null,
	_initCustomFieldsEditor: function() {
		$('.person-custom-fields-edit:first', this.wrapper).click((function() {
			this.showCustomFieldEditor();
		}).bind(this));

		this.custom_fields_display = $('.person-custom-fields:not(.edit)', this.wrapper);
		this.custom_fields_edit = $('.person-custom-fields.edit', this.wrapper).detach().appendTo($('body'));

		$('.close-trigger', this.custom_fields_edit).click((function() {
			this.closeCustomFieldEditor();
		}).bind(this));

		var self = this;
		$('.save-trigger', this.custom_fields_edit).click((function() {
			var fieldEls = $(':input', self.custom_fields_edit);
			this._saveCustomFields(fieldEls);
		}).bind(this));
	},

	showCustomFieldEditor: function() {

		var width = this.custom_fields_display.width();
		if (width < 350) width = 350;

		this.custom_fields_edit.css({
			position: 'absolute',
			width: width,
			'z-index': this.zIndex
		});

		this.custom_fields_edit.position({
			my: 'right top',
			at: 'right top',
			of: $('.properties-info-list-wrap', this.wrapper)
		});

		this.custom_fields_edit.slideDown();
	},

	closeCustomFieldEditor: function() {
		this.custom_fields_edit.slideUp();
	},

	_saveCustomFields: function(fieldEls) {
		$('.buttons .loading-off', this.custom_fields_edit).hide();
		$('.buttons .loading-on', this.custom_fields_edit).show();

		var data = fieldEls.serializeArray();

		$.ajax({
			url: this.getMetaData('saveFieldsUrl'),
			type: 'POST',
			context: this,
			data: data,
			dataType: 'json',
			success: function(data) {
				this._handleSaveCustomFieldsSuccess(data);
			}
		});
	},

	_handleSaveCustomFieldsSuccess: function(data) {
		$('.buttons .loading-on', this.custom_fields_edit).hide();
		$('.buttons .loading-off', this.custom_fields_edit).show();
		this.closeCustomFieldEditor();

		$('.wrap', this.custom_fields_display).html(data.custom_fields_html);

		var uglist = $('ul.usergroups-list', this.wrapper).html('<li>' + data.usergroup_names.join('</li><li>') + '</li>');

		// Make sure usergroups list is shown/hidden if there are groups
		var ugwrapper = $('.usergroups-list-wrap', this.wrapper);
		if ($('li', uglist).length) {
			ugwrapper.show();
		} else {
			ugwrapper.hide();
		}
	},

	//#########################################################################
	//# Labels
	//#########################################################################

	labelsList: null,
	_initLabels: function() {
		// Tags
		this.labelsList = $(".people-tags ul", this.wrapper).tagit({
			availableTags: this.getMetaData('labelsAutocompleteUrl'),
			enableBackspace: false,
			fieldName: 'labels',
			onchange: this.saveLabels.bind(this)
		});
	},

	_saveLabelsTimeout: null,
	saveLabels: function() {
		if (this._saveLabelsTimeout) {
			window.clearTimeout(this._saveLabelsTimeout);
		}

		this._saveLabelsTimeout = this._doSaveLabels.delay(2000, this);
	},

	_doSaveLabels: function() {
		var data = $(':input', this.labelsList).serializeArray();

		$.ajax({
			url: this.getMetaData('labelsSaveUrl'),
			type: 'POST',
			context: this,
			data: data,
			dataType: 'json',
			success: function(data) {
				this._handleSaveLabelsSuccess(data);
			}
		});
	},

	_handleSaveLabelsSuccess: function(data) {

	},

	//#########################################################################
	//# Company stuff
	//#########################################################################

	initOrgEditable: function() {

		this.org_dlg = $('.org-edit-dlg', this.wrapper).detach().appendTo($('body'));

		$('.close-trigger', this.org_dlg).click((function() {
			this.closeOrgEditor();
		}).bind(this));

		var self = this;
		$('.save-trigger', this.org_dlg).click((function() {
			var fieldEls = $(':input', self.org_dlg);
			this.saveOrg();
		}).bind(this));

		$('.organization.hover-edit:first', this.wrapper).dblclick((function() {
			this.showOrgEditor();
		}).bind(this));
	},

	showOrgEditor: function() {

		var width = this.org_dlg.width();
		if (width < 350) width = 350;

		this.org_dlg.css({
			position: 'absolute',
			width: width,
			'z-index': this.zIndex
		});

		this.org_dlg.position({
			my: 'left top',
			at: 'left top',
			of: $('.organization.hover-edit:first', this.wrapper)
		});

		this.org_dlg.slideDown();
	},

	closeOrgEditor: function() {
		this.org_dlg.slideUp();
	},

	saveOrg: function() {

		var sel = $('select[name="organization_id"]', this.org_dlg);
		var opt = $('option:selected', sel);

		var data = {
			'organization_id': opt.val(),
			'organization_position': $('input[name="organization_position"]', this.org_dlg).val()
		};

		$.ajax({
			timeout: 20000,
			type: 'POST',
			url: BASE_URL + 'agent/people/' + this.meta.person_id + '/ajax-save-organization',
			data: data,
			success: this.handleOrgSave.bind(this)
		});
	},

	handleOrgSave: function(data) {
		$('.organization.hover-edit:first .name').html(data.organization_name);
		$('.organization.hover-edit:first .position').html(data.organization_position);
		this.closeOrgEditor();
	},

	//#########################################################################
	//# Note form stuff
	//#########################################################################

	initNoteFormEditable: function() {
		this.notesSection = $('.notes-wrap:first', this.wrapper);
		
		$('.new-note-form .trigger.save', this.notesSection).click((function() {
			this.saveNote();
		}).bind(this));
	},

	saveNote: function() {

		$('.new-note-form').addClass('saving');
		var note = $('.new-note-form textarea').val();

		$.ajax({
			timeout: 20000,
			type: 'POST',
			url: BASE_URL + 'agent/people/' + this.meta.person_id + '/ajax-save-note',
			data: {note: note},
			success: this.handleNoteSave.bind(this)
		});
	},

	handleNoteSave: function(data) {

		var list = $('.note-list', this.notesSection);
		list.prepend(data.note_li_html);

		$('.new-note-form').removeClass('saving');

		this.updateCounts();
	},

	//#########################################################################
	//# Note pagination
	//#########################################################################

	initNotePagination: function() {
		var pages = $('ul.pages', this.notesSection);
		if (!pages.length) return;

		var self = this;
		$('li', pages).click(function() {
			self.loadNotePage($(this).data('page'));
		});
	},

	loadNotePage: function(page) {
		$.ajax({
			timeout: 20000,
			type: 'POST',
			url: BASE_URL + 'agent/people/' + this.meta.person_id + '/ajax-get-notes',
			data: { 'pp': $('.note-list', this.notesSection).data('limit'), 'p': page },
			success: this.handleGetNotes.bind(this)
		});
	},

	handleGetNotes: function(data) {
		var note_list = $('.note-list', this.notesSection);
		note_list.html(data.notes_html);

		$('ul.pages il', this.notesSection).removeClass('active');
		$('ul.pages il.page-'+data.page, this.notesSection).addClass('active');
	},

	//#########################################################################
	//# Contact form stuff
	//#########################################################################

	startContactAdd: function(type) {

		var edit_el = $('.contact-add-tpl.new.'+type, this.wrapper).clone();
		this.wrapper.append(edit_el)

		var pos_el = $('.contact-info-list-wrap:first', this.wrapper);
		var pos = pos_el.position();

		// Initial positioning
		// Because .position() needs to work on visible
		// element, which might cause scrolling
		edit_el.css({
			position: 'absolute',
			top: 10,
			left: 10,
			'z-index': this.zIndex
		});
		edit_el.show();

		edit_el.position({
			my: 'right top',
			at: 'right top',
			of: pos_el
		});

		$('.close', edit_el).click(function() {
			edit_el.remove();
		});

		var self = this;
		$('.save', edit_el).click(function() {
			self.saveContact(edit_el);
		});
	},

	saveContact: function(edit_el) {

		var data = $(':input, select, textarea', edit_el).serializeArray();

		edit_el.addClass('saving');

		$.ajax({
			timeout: 20000,
			type: 'POST',
			url: BASE_URL + 'agent/people/' + this.meta.person_id + '/ajax-save-contact',
			data: data,
			success: (function(data) {
				this.handleSaveSuccess(data, edit_el);
			}).bind(this)
		});

	},

	handleSaveSuccess: function(data, edit_el) {
		$('.contact-info-list-wrap:first', this.wrapper).html(data.contact_html);

		edit_el.remove();
	},

	//#########################################################################
	//# Email Dlg stuff
	//#########################################################################

	initEmailDlg: function() {

		this.email_dlg = $('.email-edit-dlg', this.wrapper).detach().appendTo($('body'));

		$('.close-trigger', this.email_dlg).click((function() {
			this.closeEmailEditor();
		}).bind(this));

		var self = this;
		$('.save-trigger', this.email_dlg).click((function() {
			this.saveEmails();
		}).bind(this));

		$('.organization.hover-edit:first', this.wrapper).dblclick((function() {
			this.showEmailEditor();
		}).bind(this));

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

	showEmailEditor: function() {

		var width = this.email_dlg.width();
		if (width < 350) width = 350;

		this.email_dlg.css({
			position: 'absolute',
			width: width,
			'z-index': this.zIndex
		});

		this.email_dlg.position({
			my: 'left top',
			at: 'left top',
			of: $('.contact-info-list-wrap:first', this.wrapper)
		});

		this.email_dlg.slideDown();
	},

	closeEmailEditor: function() {
		this.email_dlg.slideUp();
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
			url: BASE_URL + 'agent/people/' + this.meta.person_id + '/ajax-save-emails',
			data: data,
			success: this.handleEmailSave.bind(this)
		});
	},

	handleEmailSave: function(data) {
		$('ul.emails-list', this.email_dlg).empty().html(data.dlg_html);

		var html = '<li>' + data.emails_list.join('</li><li>') + '</li>';

		$('.contact-info-list-wrap:first ul.emails-list:first', this.wrapper).html(html);

		this.closeEmailEditor();
	}
});