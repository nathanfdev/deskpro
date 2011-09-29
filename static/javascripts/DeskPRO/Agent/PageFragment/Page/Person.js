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
		cw.tinyscrollbar();
		$('div.scroll-content:first, div.scroll-viewport:first', this.contentWrapper).resize(function() {
			// When size changes within the pane, need to re-size the scroll
			cw.tinyscrollbar_update();
		});

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
			}
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
			url: BASE_URL + 'agent/people/' + this.meta.person_id + '/ajax-save-note',
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
