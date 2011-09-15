Orb.createNamespace('DeskPRO.Agent.PageFragment.Page');
DeskPRO.Agent.PageFragment.Page.Organization = new Orb.Class({

	Extends: DeskPRO.Agent.PageFragment.Basic,

	initializeProperties: function() {
		this.parent();
		this.TYPENAME = 'organization';
	},

	initPage: function(el) {
		this.wrapper = el;
		this.contentWrapper = $('div.layout-content:first', el);

		var self = this;

		var cw = this.contentWrapper;
		cw.tinyscrollbar();
		$('div.scroll-content:first, div.scroll-viewport:first', this.contentWrapper).resize(function() {
			// When size changes within the pane, need to re-size the scroll
			cw.tinyscrollbar_update();
		});

		this.contactEditor = new DeskPRO.Agent.PageFragment.Page.PersonHelper.ContactEditor(this, {
			saveUrl: BASE_URL + 'agent/organizations/' + this.meta.org_id + '/save-contact-data.json'
		});
		this.ownObject(this.contactEditor);

		this.initNoteFormEditable();

		this.initTimesOnCollection($('time.timeago', this.wrapper));

		// Name is editable
		var name = $('h3.name.editable:first', el);
		if (!name.attr('id')) {
			name.attr('id', Orb.getUniqueId());
		}

		var editable = new DeskPRO.Form.InlineEdit({
			baseElement: this.wrapper,
			ajax: {
				url: BASE_URL + 'agent/organizations/' + this.meta.org_id + '/ajax-save'
			}
		});

		// Attach click to wrapper because
		// this same code is used on popout on ticket,
		// and clicks dont bubble to document click
		$(this.wrapper).click(function (ev) {
			editable.handleDocumentClick(ev);
		});

		this.moreactionsMenu = new DeskPRO.UI.Menu({
			triggerElement: $('.more', this.getEl('action_buttons')),
			menuElement: this.getEl('more_actions_menu'),
			onItemClicked: function(info) {
				var action = $(info.itemEl).data('action');
			}
		});
		this.ownObject(this.moreactionsMenu);

		this.changePic = new DeskPRO.Agent.PageFragment.Page.PersonHelper.ChangePic(this, {
			loadUrl: BASE_URL + "agent/organizations/" + this.meta.org_id + "/change-picture-overlay",
			saveUrl: BASE_URL + 'agent/organizations/' + this.meta.org_id + '/ajax-save'
		});
		this.ownObject(this.changePic);

		this._initLabels();
		this._initCustomFieldsEditor();

		this.getEl('members_list').delegate('.remove', 'click', function() {
			var row = $(this).closest('.member-row');
			var personId = row.data('person-id');
			if (!personId) {
				return;
			}

			row.fadeOut('fast');

			$.ajax({
				url: BASE_URL + 'agent/organizations/' + self.meta.org_id + '/ajax-save',
				data: { action: 'remove-person', person_id: personId },
				type: 'POST',
				error: function() {
					row.show();
				},
				success: function() {
					row.remove();
				}
			});
		});

		this.getEl('newmember_person_input').autocomplete({
			focus: true,
			delay: 300,
			minLength: 2,
			source: function(req, callback) {
				$.ajax({
					timeout: 8000,
					type: 'POST',
					url: BASE_URL + 'agent/people-search/search-quick',
					data: {term: req.term, format: 'json', limit: 20},
					dataType: 'json',
					context: this,
					success: function(data) {
						callback(data);
					}
				});
			},
			select: (function(ev, ui) {
				ev.preventDefault();
				self.getEl('newmember_person_input').val(ui.item.email);
				self.getEl('newmember_person_id').val(ui.item.value);
			}).bind(this)
		});

		this.getEl('newmember_btn').click(function() {
			var personId = self.getEl('newmember_person_id').val();
			var pos = self.getEl('newmember_position').val();

			$.ajax({
				url: BASE_URL + 'agent/organizations/' + self.meta.org_id + '/ajax-save',
				data: { action: 'add-person', person_id: personId, position: pos },
				type: 'POST',
				success: function(data) {
					self.getEl('newmember_person_input').val('');
					self.getEl('newmember_position').val('');
					self.getEl('newmember_person_id').val('0');

					var row = $(data.row_html);
					row.insertAfter(self.getEl('newmember_row'));

					DeskPRO_Window.util.showSavePuff(row);
				}
			});
		});
	},

	//#########################################################################
	//# Custom fields
	//#########################################################################

	_initCustomFieldsEditor: function() {

		var fieldsRenderedWrap, fieldsEditWrap;

		fieldsRenderedWrap = this.fieldsRenderedWrap = this.getEl('custom_fields_rendered');
		fieldsEditWrap = this.fieldsEditWrap = this.getEl('custom_fields_editable');

		var toggle = (function() {
			if (fieldsRenderedWrap.is(':visible')) {
				fieldsRenderedWrap.hide();
				fieldsEditWrap.show();
			} else {
				fieldsEditWrap.hide();
				fieldsRenderedWrap.show();
			}
		}).bind(this);

		$('.show-edit-custom-fields', this.wrapper).click(function() {
			toggle();
		});

		$('.save-custom-fields', this.wrapper).click((function() {
			var formData = $('input, select, textarea', fieldsEditWrap).serializeArray();

			$.ajax({
				url: BASE_URL + 'agent/organizations/' + this.meta.org_id + '/ajax-save-custom-fields',
				type: 'POST',
				data: formData,
				dataType: 'html',
				success: function(rendered) {
					fieldsRenderedWrap.empty().html(rendered);
					toggle();
				}
			});
		}).bind(this));
	},

	//#########################################################################
	//# Labels
	//#########################################################################

	_initLabels: function() {
		// Tags
		this.labelsList = $(".org-tags ul", this.wrapper).tagit({
			availableTags: this.getMetaData('labelsAutocompleteUrl'),
			enableBackspace: false,
			fieldName: 'labels',
			onchange: this.saveLabels.bind(this)
		});
	},

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

			}
		});
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

		$('.new-note-form', this.notesSection).addClass('saving');
		var note = $('.new-note-form textarea', this.notesSection).val();

		$.ajax({
			timeout: 20000,
			type: 'POST',
			url: BASE_URL + 'agent/organizations/' + this.meta.org_id + '/ajax-save-note',
			data: {note: note},
			success: this.handleNoteSave.bind(this)
		});
	},

	handleNoteSave: function(data) {

		$('.new-note-form textarea', this.notesSection).val('');

		var list = $('.note-list', this.notesSection);
		list.append(data.note_li_html);

		$('.new-note-form', this.notesSection).removeClass('saving');

		this.updateCounts();
	}
});
