Orb.createNamespace('DeskPRO.Agent.PageFragment.Page');

DeskPRO.Agent.PageFragment.Page.NewOrganization = new Orb.Class({

	Extends: DeskPRO.Agent.PageFragment.Basic,

	initializeProperties: function() {
		this.parent();
		this.TYPENAME = 'neworganization';
		this.allowDupe = true;
    this.jsfields = {};
	},

	initPage: function(el) {
		var self = this;
		this.wrapper = el;
		this.parent(el);
		this.isSaving = false;

		this.form = $('form', this.wrapper).on('submit', function(ev) {
			ev.preventDefault();
		});

		$('button.submit-trigger', this.wrapper).on('click', this.submit.bind(this));

		this._initNameSection();
		this._initOtherSection();
		this._initJavascriptCustomFields();

		this.stateSaver = new DeskPRO.Agent.PageHelper.StateSaver({
			stateId: 'neworg',
			listenOn: this.getEl('neworg')
		});
		this.ownObject(this.stateSaver);
    this.customFieldsUpload = new DeskPRO.Agent.PageHelper.CustomFieldUpload(this.wrapper);
		this.ownObject(this.customFieldsUpload);
	},

	markForReload: function() {
		if (!this.markedForReload) {
			this.markedForReload = true;
			this.addEvent('deactivate', this.closeSelf.bind(this));
		}
	},

	closeSelf: function() {
		var ev = {cancel: false};
		this.fireEvent('closeSelf', [ev]);

		if (!ev.cancel) {
			this.parent();
		}
	},

	submit: function() {
	  if (this.isSaving) {
	    return;
    }

	  this.isSaving = true;
		var self = this;
		var formData = this.form.serializeArray();

		this.labelsInput && (this.labelsInput.getLabels() || []).forEach(function(label){
			formData.push({
				name: 'neworg[labels][]',
				value: label.replace(/\r?\n/g, "\r\n")
			});
		});

		$.ajax({
			url: BASE_URL + 'agent/organizations/new/save',
			type: 'POST',
			data: formData,
			dataType: 'json',
			context: this,
			success: function(data) {
				if (data.success) {
					DeskPRO_Window.runPageRoute('person:' + BASE_URL + 'agent/organizations/' + data.org_id);

					$('select.dp-org-select').each(function() {
						var opt = $('<option />');
						opt.val(data.org_id);
						opt.text(self.getEl('name').val());

						$(this).append(opt);
					});

					this.closeSelf();
				} else {
					if (data && data.error_code) {
					  if (data.error_code === 'invalid_name') {
              DeskPRO_Window.showAlert('Please enter a name for the organization');
            } else if (data.error_code === 'free') {
              DeskPRO_Window.showAlert(data.error_messages.join(". "));
            }
					}
				}
        this.isSaving = false;
      }
		});
	},

	//#################################################################
	//# Name/email section
	//#################################################################

	_initNameSection: function() {

	},

	//#########################################################################
	//# Other Section
	//#########################################################################

	_initOtherSection: function() {

		var self = this;
		this.otherTabs = new DeskPRO.UI.SimpleTabs({
			triggerElements: $('li', this.getEl('other_props_tabs')),
			context: this.getEl('other_props_tabs_content'),
			autoSelectFirst: false,
			onTabSwitch: function(eventData) {
				if (!self.labelsInput && eventData.tabContent.hasClass('tab-properties') && self.getEl('labels_input')[0]) {
					self.labelsInput = new DeskPRO.UI.LabelsInput({
						type: 'organizations',
						input: self.getEl('labels_input')
					});
					self.ownObject(self.labelsInput);
				}
			},
			onTabClick: (function(ev) {
				var contentWrap = this.getEl('other_props_tabs_content');
				var navWrap = this.getEl('other_props_tabs_wrap');
				var tab = ev.tabEl;

				// Toggle content state if we're clicking for the first time,
				// or re-clicking a tab
				if (!$('.on', navWrap).length || tab.is('.on')) {
					if (contentWrap.is(':visible')) {
						contentWrap.hide();
						navWrap.removeClass('on');
					} else {
						contentWrap.show();
						navWrap.addClass('on');
					}
				}
			}).bind(this)
		});
		this.ownObject(this.otherTabs);
	},

  _initJavascriptCustomFields: function () {
    var self = this;
    self.wrapper.find('.js-custom-field').each(function () {
      var $el       = $(this);
      var code      = $el.data('code');
      var $field    = $el.find('input.js-custom-field-hidden-input');
      var fieldData = JSON.parse($field.val() ? $field.val() : "{}") || {"value": null, "data": {}};
      var fieldId   = $field.data('field-id');
      var evCode = function(){};
      var fullCode = "evCode = " + code;
      eval(fullCode);
      var ctx = {
        jQuery:      $,
        Handlebars:  Handlebars,
        'interface': 'agent',
        context:     'newticket',
        ticket:      {},
        person:      {},
      };
      evCode(ctx);

      self.jsfields[fieldId] = {
        ctx:          ctx,
        element:      null,
        field:        $field,
        currentData:  fieldData.data || {},
        currentValue: fieldData.value,
      };

      var field = self.jsfields[fieldId];

      var $renderedElement = field.ctx.renderField.call(field.ctx, function (value, data) {
        var dataObject = { value: null, data: null };
        if (
          (value === null || typeof value === "undefined")
          && (data === null || typeof data === "undefined")
        ) {
          dataObject.value = null;
          dataObject.data  = null;
        } else {
          dataObject = Object.assign({}, { value: value }, { data: data || {} });
        }
        field.field.val(JSON.stringify(dataObject));
        field.currentData = dataObject.data;
        field.currentValue = dataObject.value;
      }, field.currentValue, field.currentData);
      field.field.after($renderedElement);
    });
  }
});
