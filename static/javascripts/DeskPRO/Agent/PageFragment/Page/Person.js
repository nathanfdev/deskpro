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

		this.initEditorOverlay();

		this.initNoteFormEditable();

		this.initRoutesOnCollection($('.with-route', this.wrapper));
		this.initTimesOnCollection($('time.timeago', this.wrapper));

		var tzMenu = new DeskPRO.UI.Menu({
			menuElement: this.getEl('timezone')
		});
		var autoResMenu = new DeskPRO.UI.Menu({
			menuElement: this.getEl('is_autoresponder')
		});

		this.getEl('timezone').change(function(){
			var val = $(this).val();
			$.ajax({
				url: BASE_URL + 'agent/people/' + self.meta.person_id + '/ajax-save',
				type: 'POST',
				dataType: 'json',
				data: {
					action: 'timezone',
					timezone: val
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

		this.morectionsMenu = new DeskPRO.UI.Menu({
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

		this.changePic = new DeskPRO.Agent.PageFragment.Page.PersonHelper.ChangePic(this);

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

	replaceEditorOverlay: function(html) {
		var contactEditor = $('.profile-contact-editor', this.wrapper);
		contactEditor.remove();
		contactEditor = null;

		$(html).appendTo(this.wrapper);

		this.initEditorOverlay();
	},

	initEditorOverlay: function() {

		var self = this;
		if (this.contactOverlay) {
			this.contactOverlay.destroy();
			this.contactOverlay = null;
		}

		if (this.contactNewMenu) {
			this.contactNewMenu.destroy();
			this.contactNewMenu = null;
		}

		var contactEditor = $('.profile-contact-editor', this.wrapper);

		this.contactOverlay = new DeskPRO.UI.Overlay({
			triggerElement: $('.contact-edit:first', this.wrapper),
			contentElement: contactEditor
		});

		$('.save-trigger', contactEditor).click(function(ev) {

			var formData = $(':input, select, textarea', contactEditor).serializeArray();

			$.ajax({
				url: BASE_URL + 'agent/people/' + self.meta.person_id + '/save-contact-data.json',
				type: 'POST',
				dataType: 'json',
				data: formData,
				success: function(data) {
					self.contactOverlay.close();
					$('.contact-list-wrapper', self.wrapper).empty().html(data.display_html);
					self.replaceEditorOverlay(data.editor_overlay_html);
				}
			});
		});

		contactEditor.delegate('.remove', 'click', function(ev) {
			var el = $(this);

			var row = el;
			while (!row.is('li')) {
				row = row.parent();
			}

			var removeName = row.data('remove-name');
			var removeVal  = row.data('remove-value');

			if (removeName && removeVal) {
				var input = $('<input type="hidden" />');
				input.attr('name', removeName);
				input.val(removeVal);

				input.appendTo($('.contact-edit-list', contactEditor));
			}

			row.fadeOut('fast', function() {
				row.remove();
			});
		});

		this.contactNewMenu = new DeskPRO.UI.Menu({
			triggerElement: $('.add-new-type-trigger', this.wrapper),
			menuElement: $('.add-new-type-menu:first', this.wrapper),
			initMenuNow: true,
			onItemClicked: (function(info) {
				var wrap = this.contactOverlay.elements.wrapper;
				console.log(wrap);
				var item = $(info.itemEl);
				var tpl = $('.' + item.data('tpl'), wrap).get(0).innerHTML;
				tpl = tpl.replace(/%id%/g, Orb.uuid());

				var el = $(tpl);
				el.appendTo($('.contact-edit-list ul', wrap));
			}).bind(this)
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
