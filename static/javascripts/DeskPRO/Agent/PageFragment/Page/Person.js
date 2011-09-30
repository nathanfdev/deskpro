Orb.createNamespace('DeskPRO.Agent.PageFragment.Page');
DeskPRO.Agent.PageFragment.Page.Person = new Orb.Class({

	Extends: DeskPRO.Agent.PageFragment.Basic,

	initializeProperties: function() {
		this.parent();
		this.TYPENAME = 'person';
	},

	initPage: function(el) {
		this.wrapper = el;
		this.contentWrapper = $('div.layout-content:first', el);

		this.zIndex = 999999;

		var self = this;

		var cw = this.contentWrapper;

		this.contactEditor = new DeskPRO.Agent.PageFragment.Page.PersonHelper.ContactEditor(this, {
			saveUrl: BASE_URL + 'agent/people/' + this.meta.person_id + '/save-contact-data.json'
		});
		this.ownObject(this.contactEditor);

		this.initNoteFormEditable();

		this.initTimesOnCollection($('time.timeago', this.wrapper));

		var tzMenu = new DeskPRO.UI.Menu({
			menuElement: this.getEl('timezone')
		});
		this.ownObject(tzMenu);

		var autoResMenu = new DeskPRO.UI.Menu({
			menuElement: this.getEl('is_autoresponder')
		});
		this.ownObject(autoResMenu);

		this.getEl('timezone').change(function(){
			var val = $(this).val();
			$('.timezone-info', this.wrapper).empty();
			$.ajax({
				url: BASE_URL + 'agent/people/' + self.meta.person_id + '/ajax-save',
				type: 'POST',
				dataType: 'json',
				data: {
					action: 'timezone',
					timezone: val
				},
				context: this,
				success: function(data) {
					$('.timezone-info', this.wrapper).empty().html(data.bit_html);
				}
			});
		});

		this.getEl('is_autoresponder').change(function(){
			var val = $(this).val();
			$.ajax({
				url: BASE_URL + 'agent/people/' + self.meta.person_id + '/ajax-save',
				type: 'POST',
				dataType: 'json',
				data: {
					action: 'is_autoresponder',
					is_autoresponder: val
				}
			});
		});

		// Name is editable
		var name = $('h3.name.editable:first', el);
		if (!name.attr('id')) {
			name.attr('id', Orb.getUniqueId());
		}

		var editable = new DeskPRO.Form.InlineEdit({
			baseElement: this.wrapper,
			ajax: {
				url: BASE_URL + 'agent/people/' + this.meta.person_id + '/ajax-save'
			},
			triggers: '.edit-name-gear'
		});

		// Attach click to wrapper because
		// this same code is used on popout on ticket,
		// and clicks dont bubble to document click
		$(this.wrapper).click(function (ev) {
			editable.handleDocumentClick(ev);
		});

		$('.create-ticket', this.getEl('action_buttons')).click(function() {
			DeskPRO_Window.newTicketLoader.open(function(page) {
				page.setUser(self.meta.person_id);
			});
		});

		$('.contact-list-wrapper', this.wrapper).first().delegate('.set-primary', 'click', function() {
			var email_id = $(this).data('email-id');
			$('.contact-list-wrapper .email.is-primary', self.wrapper).removeClass('is-primary');
			$('.contact-list-wrapper .email-' + email_id, self.wrapper).addClass('is-primary');

			var val = $(this).val();
			$.ajax({
				url: BASE_URL + 'agent/people/' + self.meta.person_id + '/ajax-save',
				type: 'POST',
				dataType: 'json',
				data: {
					action: 'set-primary-email',
					email_id: email_id
				}
			});
		});

		this.moreactionsMenu = new DeskPRO.UI.Menu({
			triggerElement: $('.more', this.getEl('action_buttons')),
			menuElement: this.getEl('more_actions_menu'),
			onItemClicked: function(info) {
				var action = $(info.itemEl).data('action');

				if (action == 'reset-password') {
					DeskPRO_Window.showPrompt(
						'<div>Enter a new password:<br /><br /><label style="font-size: 11px;"><input type="checkbox" class="send_email" value="1" /> Send the user an email with their new password</label></div>',
						function(val, wrap) {
							var postData = [];
							postData.push({
								name: 'password',
								value: val
							});

							postData.push({
								name: 'send_email',
								value: $('.send_email', wrap).is(':checked')
							});

							postData.push({
								name: 'action',
								value: 'password'
							});

							$.ajax({
								url: BASE_URL + 'agent/people/' + self.meta.person_id + '/ajax-save',
								type: 'POST',
								dataType: 'json',
								data: postData
							});
						}
					);
				}
			}
		});
		this.ownObject(this.moreactionsMenu);

		this.changePic = new DeskPRO.Agent.PageFragment.Page.PersonHelper.ChangePic(this, {
			loadUrl: BASE_URL + "agent/people/" + this.meta.person_id + "/change-picture-overlay",
			saveUrl: BASE_URL + 'agent/people/' + this.meta.person_id + '/ajax-save'
		});
		this.ownObject(this.changePic);

		this._initLabels();
		this._initCustomFieldsEditor();

		$('.profile-box-container.tabbed', this.wrapper).each(function() {
			var simpleTabs = new DeskPRO.UI.SimpleTabs({
				triggerElements: '> header li',
				context: this
			});

			self.ownObject(simpleTabs);
		});

		this._initOrgEdit();

		this.getEl('tickets_viewall').click(function(ev){
			var row = $(this).closest('tr').remove();
			self.getEl('tickets_rest').slideDown();
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
				url: BASE_URL + 'agent/people/' + self.meta.person_id + '/ajax-save',
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
	//# Org Edit
	//#########################################################################

	_initOrgEdit: function() {
		var self = this;
		$('.org-edit-trigger', this.wrapper).click(function(ev) {
			ev.preventDefault();
			self.toggleOrgEdit();
		});

		var orgDisplay = this.getEl('org_display_wrap');
		var orgEdit    = this.getEl('org_edit_wrap');

		this.getEl('org_edit_save').click(function() {
			var postData = [];
			postData.push({
				name: 'action',
				value: 'set-organization'
			});
			postData.push({
				name: 'name',
				value: $('.org-set', self.getEl('org_edit_wrap')).val().trim()
			});
			postData.push({
				name: 'position',
				value: $('.org-pos-set', self.getEl('org_edit_wrap')).val().trim()
			});

			$.ajax({
				url: BASE_URL + 'agent/people/' + self.meta.person_id + '/ajax-save',
				type: 'POST',
				data: postData,
				dataType: 'json',
				success: function(data) {
					orgDisplay.empty();
					if (data.organization_id) {
						orgDisplay.html(data.html);
					}

					self.toggleOrgEdit();
				}
			});
		});

		var currentLookupAjax = null;

		var showhide_notice = function(onff) {
			if (onff == 'on') {
				self.getEl('org_create_notice').slideDown();
			} else {
				self.getEl('org_create_notice').slideUp();
			}
		};

		var getname = function() {
			return $('.org-set', self.getEl('org_edit_wrap')).val().trim();
		}

		var runlookup = function() {
			if (currentLookupAjax) {
				currentLookupAjax.abort();
				currentLookupAjax = null;
			}

			var val = getname();

			currentLookupAjax = $.ajax({
				url: BASE_URL + 'agent/organization-search/name-lookup.json',
				dataType: 'json',
				data: {'name': val},
				success: function(data) {
					if (getname() != val) {
						return;
					}
					if (data.organization_id) {
						showhide_notice('off');
					} else {
						showhide_notice('on');
					}
				}
			});
		};

		$('.org-set', this.getEl('org_edit_wrap')).autocomplete({
			minLength: 2,
			source: function(request, response) {
				var name = getname();
				$.ajax({
					url: BASE_URL + 'agent/organization-search/quick-name-search.json',
					data: { 'term': request.term },
					dataType: 'json',
					success: function(data) {
						response(data.results);

						if (getname() == name) {
							if (!data.exact) {
								showhide_notice('on');
							} else {
								showhide_notice('off');
							}
						} else {
							showhide_notice('off');
						}
					}
				})
			}
		}).blur(function() {
			runlookup();
		});
	},

	toggleOrgEdit: function() {
		var orgDisplay = this.getEl('org_display_wrap');
		var orgEdit    = this.getEl('org_edit_wrap');

		if (orgEdit.is(':visible')) {
			orgEdit.hide();
			orgDisplay.show();
		} else {
			orgDisplay.hide();
			orgEdit.show();
		}
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
		}).bind(this);;

		$('.show-edit-custom-fields', this.wrapper).click(function() {
			toggle();
		});

		$('.save-custom-fields', this.wrapper).click((function() {
			var formData = $('input, select, textarea', fieldsEditWrap).serializeArray();

			$.ajax({
				url: BASE_URL + 'agent/person/' + this.meta.person_id + '/ajax-save-custom-fields',
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
		this.labelsList = $(".people-tags ul", this.wrapper).tagit({
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
			url: BASE_URL + 'agent/people/' + this.meta.person_id + '/ajax-save-note',
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
