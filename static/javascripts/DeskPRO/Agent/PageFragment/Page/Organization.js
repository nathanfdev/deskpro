Orb.createNamespace('DeskPRO.Agent.PageFragment.Page');
DeskPRO.Agent.PageFragment.Page.Organization = new Class({

	Extends: DeskPRO.Agent.PageFragment.Basic,

	TYPENAME: 'organiztion',

	wrapper: null,

	contactSection: null,

	notesSection: null,

	initPage: function(el) {

		this.wrapper = el;

		var self = this;

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
				url: BASE_URL + 'agent/organizations/' + this.meta.organization_id + '/ajax-save'
			}
		});

		// Attach click to wrapper because
		// this same code is used on popout on ticket,
		// and clicks dont bubble to document click
		$(this.wrapper).click(function (ev) {
			editable.handleDocumentClick(ev);
		});

		//edit-contact-info
		this.editContactInfoMenu = new DeskPRO.UI.Menu({
			triggerElement: $('.edit-contact-info:first', this.wrapper),
			menuElement: $('.contact-info-edit-menu:first', this.wrapper),
			onItemClicked: function(info) {
				var type = $(info.itemEl).data('edit-type');
				if (type == 'email') {
					self.email_dlg.dialog('open');
				} else {
					self.startContactAdd(type);
				}
			}
		});
		
		this.initNoteFormEditable();
		this.initNotePagination();
		this._initLabels();
		this._initCustomFieldsEditor();

		this.initRoutesOnCollection($('.with-route'));
	},

	destroyPage: function() {

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
			'z-index': 10000
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
	},

	//#########################################################################
	//# Labels
	//#########################################################################

	labelsList: null,
	_initLabels: function() {
		// Tags
		this.labelsList = $(".org-tags ul", this.wrapper).tagit({
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
	//# Note form stuff
	//#########################################################################

	initNoteFormEditable: function() {
		this.notesSection = $('.notes-wrap:first', this.wrapper);

		$('.trigger.new-note', this.notesSection).click((function() {
			this.openNoteEdtiable();
		}).bind(this));

		$('.new-note-form .trigger.cancel', this.notesSection).click((function(ev) {
			ev.preventDefault();
			this.closeNoteEditable();
		}).bind(this));

		$('.new-note-form .trigger.save', this.notesSection).click((function() {
			this.saveNote();
		}).bind(this));
	},

	openNoteEdtiable: function() {
		$('.trigger.new-note', this.notesSection).hide();

		var form = $('.new-note-form', this.notesSection);
		if (form.is(':hidden')) {
			$('.new-note-form textarea', this.notesSection).val('');
			form.slideDown();
		}
	},

	closeNoteEditable: function() {

		$('.new-note-form textarea').val('');
		var form = $('.new-note-form', this.notesSection);
		if (form.is(':visible')) {
			form.slideUp();
		}

		$('.trigger.new-note', this.notesSection).show();
	},

	saveNote: function() {

		$('.new-note-form').addClass('saving');
		var note = $('.new-note-form textarea').val();

		$.ajax({
			timeout: 20000,
			type: 'POST',
			url: BASE_URL + 'agent/organizations/' + this.meta.organization_id + '/ajax-save-note',
			data: {note: note},
			success: this.handleNoteSave.bind(this)
		});
	},

	handleNoteSave: function(data) {

		var list = $('.note-list', this.notesSection);
		list.prepend(data.note_li_html);

		$('.new-note-form').removeClass('saving');
		this.closeNoteEditable();
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
			url: BASE_URL + 'agent/organizations/' + this.meta.organization_id + '/ajax-get-notes',
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
			left: 10
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
			url: BASE_URL + 'agent/organizations/' + this.meta.organization_id + '/ajax-save-contact',
			data: data,
			success: (function(data) {
				this.handleSaveSuccess(data, edit_el);
			}).bind(this)
		});

	},

	handleSaveSuccess: function(data, edit_el) {
		$('.contact-info-list-wrap:first', this.wrapper).html(data.contact_html);

		edit_el.remove();
	}
});