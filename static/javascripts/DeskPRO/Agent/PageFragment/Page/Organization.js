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

		this.contactEditor = new DeskPRO.Agent.PageFragment.Page.PersonHelper.ContactEditor(this, {
			saveUrl: BASE_URL + 'agent/organizations/' + this.meta.org_id + '/save-contact-data.json'
		});
		this.ownObject(this.contactEditor);

		this.initNoteFormEditable();

		// Name is editable
		var name = $('h3.name.editable:first', el);
		if (!name.attr('id')) {
			name.attr('id', Orb.getUniqueId());
		}

		var editable = new DeskPRO.Form.InlineEdit({
			baseElement: this.wrapper,
			ajax: {
				url: BASE_URL + 'agent/organizations/' + this.meta.org_id + '/ajax-save'
			},
			triggers: '.edit-name-gear'
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
				context: this,
				error: function() {
					row.show();
				},
				success: function() {
					row.remove();
					DeskPRO_Window.util.modCountEl(self.getEl('members_count'), '-');
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
				context: this,
				success: function(data) {
					self.getEl('newmember_person_input').val('');
					self.getEl('newmember_position').val('');
					self.getEl('newmember_person_id').val('0');

					var row = $(data.row_html);
					row.insertAfter(self.getEl('newmember_row'));

					DeskPRO_Window.util.showSavePuff(row);

					DeskPRO_Window.util.modCountEl(self.getEl('members_count'), '+');
				}
			});
		});

		$('.profile-box-container.tabbed', this.wrapper).each(function() {
			var simpleTabs = new DeskPRO.UI.SimpleTabs({
				triggerElements: '> header li',
				context: this
			});

			self.ownObject(simpleTabs);
		});

		$('.new-note textarea', this.getEl('notes_tab')).TextAreaExpander(40, 225);

		var summaryTxt = this.getEl('summary').TextAreaExpander(40, 225);
		this.getEl('save_summary').click(function() {
			var btn = $(this);
			var summary = summaryTxt.val();
			var postData = [];
			postData.push({
				name: 'action',
				value: 'set-summary'
			});
			postData.push({
				name: 'summary',
				value: summary
			});

			$.ajax({
				url: BASE_URL + 'agent/organizations/' + self.meta.org_id + '/ajax-save',
				type: 'POST',
				data: postData,
				dataType: 'json',
				success: function(data) {
					DeskPRO_Window.util.showSavePuff(btn );
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
		this.notesSection = this.getEl('notes_tab');
		this.newNoteWrap = $('li.new-note', this.getEl('notes_tab'));

		$('.save-trigger', this.newNoteWrap).click((function() {
			this.saveNote();
		}).bind(this));
	},

	saveNote: function() {

		this.notesSection.addClass('loading');
		var note = $('textarea', this.newNoteWrap).val();

		$.ajax({
			timeout: 20000,
			type: 'POST',
			url: BASE_URL + 'agent/organizations/' + this.meta.org_id + '/ajax-save-note',
			data: {note: note},
			success: this.handleNoteSave.bind(this)
		});
	},

	handleNoteSave: function(data) {

		$('textarea', this.newNoteWrap).val('');

		$(data.note_li_html).insertBefore(this.newNoteWrap);

		this.notesSection.removeClass('loading');

		DeskPRO_Window.util.modCountEl(this.getEl('notes_count'), '+');
	}
});
