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
			saveUrl: BASE_URL + 'agent/people/' + this.meta.person_id + '/save-contact-data.json',
			displayEl: this.getEl('contact_display'),
			outsideEl: this.getEl('contact_outside'),
			onReplaceEditor: function() {
				self.refreshPropBox();
			}
		});
		this.ownObject(this.contactEditor);

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
						'<div>Enter a new password. The user will be notified.</div>',
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
				} else if (action == 'delete') {
					DeskPRO_Window.showConfirm(
						$('<div>Are you sure you want to delete this user? <strong class="warning">The user will be permanantly deleted</strong>. Their tickets and other resources will be removed.'),
						function() {
							$.ajax({
								url: $(info.itemEl).data('delete-url'),
								type: 'POST',
								success: function() {
									DeskPRO_Window.showAlert('The user was deleted');
								}
							});
							self.closeSelf();
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

		this.refreshPropBox();
	},

	refreshPropBox: function() {

		var contactBox = $('.profile-box-container.contact', this.el);

		var has = false;
		if ($('.contact-data-list > li', contactBox).length) {
			has = true;
		}

		if (!has && $('.outside-display > *', contactBox).length) {
			has = true;
		}

		if (!has && $('.addresses > *', contactBox).length) {
			has = true;
		}

		if (!has) {
			contactBox.addClass('no-section');
		} else {
			contactBox.removeClass('no-section');
		}
	},

	//#########################################################################
	//# Org Edit
	//#########################################################################

	_initOrgEdit: function() {
		var self = this;
		$('.org-edit-trigger, .cancel', this.getEl('org_display_header')).click(function(ev) {
			ev.preventDefault();
			self.toggleOrgEdit();
			refreshBox();

			if ($(this).is('.cancel')) {
				if (self.getEl('org_searchbox').is('.is-new')) {
					$('.org-name', self.getEl('org_edit_wrap')).val('');
				}
				self.getEl('org_searchbox').removeClass('is-new').removeClass('is-set');
			}
		});

		var orgDisplay = this.getEl('org_display_wrap');
		var orgEdit    = this.getEl('org_edit_wrap');

		//orgEnableBtn
		this.getEl('org_searchbox').bind('orgsearchboxclick', function(ev, orgId, name) {
			$('.pos-input', orgEdit).show();
			self.orgEnableBtn('save');
		}).bind('orgsearchboxcreate', function(ev, term, name) {
			$('.pos-input', orgEdit).show();
			self.orgEnableBtn('save');
		}).bind('orgsearchreverted', function(ev, term, name) {
			if (self.getEl('org_searchbox').is('.is-new') || ($('.org-id', orgEdit).val() && $('.org-id', orgEdit).val() != '0')) {
				self.orgEnableBtn('save');
			} else {
				self.orgEnableBtn('cancel');
			}
		});

		$('.pos-input', orgEdit).keyup(function() {
			if (self.getEl('org_searchbox').is('.is-new') || ($('.org-id', orgEdit).val() && $('.org-id', orgEdit).val() != '0')) {
				self.orgEnableBtn('save');
			}
		});

		var refreshBox = function() {
			var box = self.getEl('org_box');
			box.removeClass('no-section');
			if (orgEdit.is(':visible')) {

			} else {
				if (!$('> *', orgDisplay).length) {
					box.addClass('no-section');
				}
			}
		};

		var saveFn = function() {

			self.orgEnableBtn('saving');

			var postData = [];
			postData.push({
				name: 'action',
				value: 'set-organization'
			});
			postData.push({
				name: 'name',
				value: $('.org-name', self.getEl('org_edit_wrap')).val().trim()
			});
			postData.push({
				name: 'id',
				value: $('.org-id', self.getEl('org_edit_wrap')).val().trim()
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

					self.getEl('org_searchbox').removeClass('is-new').removeClass('is-set');

					self.toggleOrgEdit();

					refreshBox();
				}
			});
		};

		this.getEl('org_edit_save').click(saveFn);
		this.getEl('org_edit_remove_org').click(function() {
			$('.org-id', self.getEl('org_edit_wrap')).val('0');
			$('.org-name', self.getEl('org_edit_wrap')).val('');
			$('.pos-input', orgEdit).hide();
			saveFn();
		});

		refreshBox();
	},

	orgEnableBtn: function(name) {
		var names = ['org-edit-trigger', 'saved', 'save', 'cancel', 'is-loading', 'remove-org'];
		var els = $('.' + names.join(', .'), this.getEl('org_display_header')).hide();
		els.filter('.' + name).show();

		if (name == 'save') {
			els.filter('.cancel').show();
		} else if (name == 'cancel') {
			var orgid = $('.org-id', this.getEl('org_edit_wrap')).val();
			if (orgid && orgid != '0') {
				els.filter('.remove-org').show();
			}
		}
	},

	toggleOrgEdit: function() {
		var orgDisplay = this.getEl('org_display_wrap');
		var orgEdit    = this.getEl('org_edit_wrap');

		if (orgEdit.is(':visible')) {
			orgEdit.hide();
			orgDisplay.show();
			this.orgEnableBtn('org-edit-trigger');
		} else {
			orgDisplay.hide();
			orgEdit.show();
			this.orgEnableBtn('cancel');
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
		this.labelsList = $(".people-tags ul", this.wrapper);

		this.labelsInput = new DeskPRO.UI.LabelsInput({
			type: 'people',
			list: this.labelsList,
			onChange: this.saveLabels.bind(this)
		});
		this.ownObject(this.labelsInput);
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
	}
});
