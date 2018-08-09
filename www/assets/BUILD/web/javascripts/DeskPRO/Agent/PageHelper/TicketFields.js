Orb.createNamespace('DeskPRO.Agent.PageHelper');

/**
 * Handles updating display based on department and rules
 */
DeskPRO.Agent.PageHelper.TicketFields = new Orb.Class({
	initialize: function(page) {
		var self = this;
		this.page = page;
    this.page.addEvent('destroy', this.destroy, this);
		this.display = this.page.getEl('field_holders').find('.field-holders-table');
		this.fieldsWithDefaultSet = {};

    this.updateDisplay = _.throttle(this.updateDisplayNow.bind(this), 300);

		this.mode = 'view';

		this.ticketReader = {
			getDepartmentId: function() {
				var catId = self.page.getEl('department_id').val();
				return parseInt(catId) || 0;
			},
			getCategoryId: function() {
				var catId = self.display.find('select.prop-input-category_id').first().val();
				return parseInt(catId) || 0;
			},
			getPriorityId: function() {
				var catId = self.display.find('select.prop-input-priority_id').first().val();
				return parseInt(catId) || 0;
			},
			getProductId: function() {
				var catId = self.display.find('select.prop-input-product').first().val();
				return parseInt(catId) || 0;
			},
			getOrganizationId: function() {
				return 0;
			},
			getWorkflowId: function() {
				var catId = self.display.find('select.prop-input-workflow_id').first().val();
				return parseInt(catId) || 0;
			},
			getFieldValue: function(name) {
				var $holders = self.page.getEl('field_holders');

				// check single fields
				var $field = $holders.find('[name="' + name + '"], [name^="' + name + '["]');
				if (!$field.length) {
          // field is not present on the form
          // e.g. org field if user doesn't belong to a org
          return;
				}

        if ($field.length === 1 && $field.is(':checkbox')) {
          return $field.is(':checked');
        }
        if ($field.attr('type') === 'hidden') {
					if ($field.val()) {
            return $field.map(function(i, el) { return el.value; }).get();
					}
          return $.trim($field.parent().text());
        }
        if ($field.is('input:not(:radio, :checkbox), textarea, select:not(.with-select2)')) {
          return $field.val();
        }
				if ($field.hasClass('with-select2')) {
					var val = $.trim($field.select2('val'));
					return val || null;
				}

				return $field.filter(':checked').map(function(i, el) { return el.value; }).get();
			},
			getTicketFieldValue: function(fieldId) {
				switch (fieldId) {
					case 'category':
						return this.getCategoryId();
					case 'workflow':
            return this.getWorkflowId();
					case 'priority':
						return this.getPriorityId();
					case 'product':
						return this.getProductId();
				}
        fieldId = ((fieldId || '')+'').replace('ticket_field_', '');
				return this.getFieldValue('custom_fields[field_' + fieldId + ']');
			},
			getUserFieldValue: function(fieldId) {
        fieldId = ((fieldId || '')+'').replace('user_field_');
				return this.getFieldValue('custom_person_fields[field_' + fieldId + ']');
			},
			getOrgFieldValue: function(fieldId) {
				fieldId = ((fieldId || '')+'').replace('org_field_');
				return this.getFieldValue('custom_org_fields[field_' + fieldId + ']');
			}
		};

    this.initDateCustomFields();
    this.initFileCustomFields();
		this.initScope(this.page.getEl('field_holders'));
		this.no_value_fields = [];

		this.page.getEl('department').on('change', function() {
			self.page.getEl('field_errors').hide();
			self.updateDisplay();
		});

		var $holders = self.page.getEl('field_holders');
		$holders.on('change dp.change', function(e){
			var name = $(e.target).attr('name');
			if (!name) return;
			if (name.indexOf('custom_fields[field_') !== -1 || name.indexOf('custom_person_fields[field_') !== -1 || name.indexOf('custom_org_fields[field_') !== -1) {
				self.updateDisplay();
			}
		});

		this.page.changeManager.addEvent('updateResult', function(data) {
			if (data.holders) {
				if (self.mode == 'view') {
					self.replaceHolders(data.holders);
          self.initFileCustomFields();
				}
			}
		});
	},

	initScope: function(el) {
		var self = this;
		var $scope = this.$scope = DeskPRO_Window.$scope.$new();

		this.oldFields = null;

		DeskPRO_Window.ngModule.dpInjector.invoke(['$compile', function($compile) {
			el.data('$ngControllerController', self);
			$compile(el.contents())(self.$scope);
		}]);

		$scope.edit_fields = [];
		this.edit_fields_map = {};
		$scope.fields = {};
		$scope.editables = {
			language: 1,
			problem: 1
		};
		$scope.hidden = [];
		$scope.savedValues = {};

		$scope.show_hidden = 0;
		$scope.is_saving = false;

		$scope.setFieldValue = function(id, value, allowDefaultValue) {
      self.display.find('.item.'+id).each(function(i, el) {
        var $el = $(el);
        value = allowDefaultValue ? $el.data('default-value') : value;
        $el.find('input[type=text], textarea, select').filter(function(){
          //filter out select2 inputs, select2 val processed below as separate call
          return $(this).parents('.with-select2').length === 0;
        }).val(value);
        $el.find('.with-select2').val(Array.isArray(value) ? value : (value+'').split(',')).change();
        $el.find('input[type=radio]').each(function(i, field) {
          var $field = $(field);

          if (!String(value) && i === 0) {
            $field.prop('checked', true);
					} else if (String($field.val()) === String(value)) {
            $field.prop('checked', true);
          } else {
            $field.prop('checked', false);
          }
        });
        $el.find('input[type=checkbox]').each(function(i, field) {
          var $field = $(field);
          if ($field.attr('name') && $field.attr('name').indexOf('[]') !== -1) {
            var vals = value ? String(value).split(',') : [];
            if (vals.indexOf(String($field.val())) !== -1) {
              $field.prop('checked', true);
            } else {
              $field.prop('checked', false);
            }
          } else {
            $field.prop('checked', value);
          }
        });

        if ($el.data('custom-field-handler') === 'file') {
        	var $fileWrapper = $el.find('.mode-edit .customfield div');
          $fileWrapper.html('');
          var $input = $($fileWrapper.data('prototype'));
          var inputName = $input.attr('name') ? $input.attr('name').replace('[__name__]', '[]') : null;

          $el.find('[data-blob-id]').each(function (i, el) {
            $fileWrapper.append($('<input type="hidden" name="'+inputName+'" value="'+$(el).data('blob-id')+'" />'));
          });
				}
      });
		};

		$scope.editField = function($event, field) {
      var $row = self.display.find('tbody.item.' + field).first();
      self.initFieldWidgets($row);

			if (!$scope.editables[field]) return;
			if ($scope.isEditMode(field)) return;
			if ($event.target.tagName === 'A') return;

			var getSelected = function() {
				if (window.getSelection) {
					return window.getSelection().toString();
				} else if (document.getSelection) {
					return document.getSelection().toString();
				} else {
					var selection = document.selection && document.selection.createRange();
					if (selection.text) {
						return selection.text.toString();
					}
					return '';
				}
				return '';
			};

			if (getSelected()) {
				return;
			}

			if (field !== 'problem') {
				var value;
				if (0 === field.indexOf('user_field_')) {
					value = self.ticketReader.getUserFieldValue(field);
				} else if (0 === field.indexOf('org_field_')) {
          value = self.ticketReader.getOrgFieldValue(field);
				} else {
          value = self.ticketReader.getTicketFieldValue(field);
				}
        $scope.savedValues[field] = value;
      }
			$scope.edit_fields.push(field);
			self.edit_fields_map[field] = true;

			// focus input field on open edit mode
			var $editContainer = $($event.currentTarget).parent().find('.mode-edit');
			var $simpleField = $editContainer.find('input[type=text], textarea, select');

			setTimeout(function() {
				if ($simpleField.length) {
					$simpleField.focus();
				}
			}, 0);
		};

		$scope.isEditMode = function(field) {
			return !!self.edit_fields_map[field];
		};

		$scope.cancelEdit = function() {
      $scope.edit_fields.forEach(function(id) {
        if (typeof $scope.savedValues[id] != 'undefined') {
          $scope.setFieldValue(id, $scope.savedValues[id]);
      	}
      	delete $scope.savedValues[id];
			});
      $scope.edit_fields.length = 0;
      self.edit_fields_map = {};
      $scope.show_hidden = 0;
      self.updateDisplay();
      self.initFileCustomFields();
		};

		$scope.saveFields = function() {
			self.saveChanges();
		};

		$scope.showHidden = function() {
			$scope.show_hidden = 1;
			for (var i = 0; i < self.no_value_fields.length; i++) {
				$scope.editField(self.no_value_fields[i]);
			}
			self.updateDisplay();
		};
	},

	updateDisplayNow: function() {
		var self = this;
		var fields = [], reader = this.ticketReader;
		var $scope = this.$scope;
		var depId = reader.getDepartmentId();
		if (window.DESKPRO_TICKET_DISPLAY) {
			fields = window.DESKPRO_TICKET_DISPLAY.getLayout(reader.getDepartmentId()).getFields();
		}

		var isVisibleByCriteria = function(f) {
      return f.checkFn ? f.checkFn(reader) && f.isVisibleOnView : f.isVisibleOnView;
		};

		$scope.editables = {
			language: 1,
			problem: 1
		};

		this.no_value_fields = [];
		var $ctrls = this.display.find('.hidden-row');
		$ctrls.removeClass('off').next().removeClass('off');

		var scopeFields = $scope.fields;
		for (var i = 0; i < fields.length; i++) {
			var f = fields[i];
			var value = true;
			if (f.field_type === 'ticket_field') {
				value = this.ticketReader.getTicketFieldValue(f.field_id);
			} else if (f.field_type === 'user_field') {
        value = this.ticketReader.getUserFieldValue(f.field_id);
			} else if (f.field_type === 'org_field') {
        value = this.ticketReader.getOrgFieldValue(f.field_id);
			}

			var noValue = !value || (value instanceof Array && value.length === 0);

			if (f.isVisibleOnEdit) {
				$scope.editables[f.id] = 1;
			}

			var visibleByCriteria = isVisibleByCriteria(f);
			var show = visibleByCriteria && (f.isVisibleOnViewAlways || !noValue || $scope.show_hidden);
			scopeFields[f.id] = !!show;

			if (visibleByCriteria && !f.isVisibleOnViewAlways && noValue) {
				this.no_value_fields.push(f.id);
			}
		}

		var newFields = [];
		Object.keys(scopeFields).forEach(function(fieldId) {
			if (scopeFields[fieldId]) {
				newFields.push(fieldId);
			}
		});

		var changed = false;
		if (!this.oldFields) {
			changed = true;
		} else if (this.oldFields.length !== newFields.length) {
			changed = true;
		} else {
			for (i = 0; i < newFields.length; i++) {
				if (newFields[i] !== this.oldFields[i]) {
					changed = true;
					break;
				}
			}
		}

		this.oldFields = newFields;

		// recursive update fields if they were changed
		if (changed) {
			this.updateDisplayNow();
			return;
		}

    $scope.fields = scopeFields;

		var changedFieldSet = false;
		if (!this.lastDepId || this.lastDepId != depId) {
      changedFieldSet = true;
		}

		for (i = 0; i < fields.length; i++) {
			f = fields[i];
			var row = this.display.find('.item.' + f.id);
			if (undefined === $scope.fields[f.id] || scopeFields[f.id]) {
				if (changedFieldSet) {
          // detach/insert is expensive, but required if
          // the order changes which it can if the dep changes
          row.detach().removeClass('off').insertBefore($ctrls);
        } else {
          row.removeClass('off');
				}
			} else {
				if (changedFieldSet) {
          row.detach().addClass('off').insertBefore($ctrls);
				} else {
          row.addClass('off');
				}
			}
		}

		var unsetField = function(name, allowDefaultValue) {
			if (!self.edit_fields_map[name]) {
        $scope.edit_fields.push(name);
        self.edit_fields_map[name] = true;
        if (!this.fieldsWithDefaultSet[name]) {
          this.fieldsWithDefaultSet[name] = true;
          $scope.setFieldValue(name, '', allowDefaultValue);
        }
      }
		};

		if (this.no_value_fields.length === $scope.hidden.length) {
			if (_.difference($scope.hidden, this.no_value_fields).length || _.difference(this.no_value_fields, $scope.hidden)) {
        $scope.hidden = this.no_value_fields.length;
			}
		} else if (this.no_value_fields.length !== $scope.hidden.length) {
      $scope.hidden = this.no_value_fields.length;
		}

		if (this.oldFields) {
			this.oldFields.forEach(function(name) {
				if (newFields.indexOf(name) === -1) {
					unsetField(name, false);
				}
			});
		}

		newFields.forEach(function(name) {
			if (self.oldFields && self.oldFields.indexOf(name) === -1) {
				unsetField(name, true);
			}
		});

    this.lastDepId = depId;
	},

	initFieldWidgets: function($tbody) {
		if (!$tbody || $tbody.data('widget-init')) return;
    $tbody.data('widget-init', 1);

		$('select', $tbody).not('.no-dp-select').dpMultiLevelSelect();
		// is it the same as one above?
		DP.select($('select', $tbody));

		$('.Date.customfield input', $tbody).each(function(){
			$(this).datetimepicker({
				format: 'L',
        locale: moment.locale(),
				widgetParent: $(this).parent().css('position', 'relative'),
				widgetPositioning: { vertical: 'bottom' },
				icons: {
					up: 'fa fa-chevron-up',
					down: 'fa fa-chevron-down',
					previous: 'fa fa-chevron-left',
					next: 'fa fa-chevron-right'
				}
			});
			$(this).on('dp.change', function(e){
				$(this).trigger('change');
			});
		});

		$('.DateTime.customfield input', $tbody).each(function(){
			$(this).datetimepicker({
				format: 'L HH:mm',
        locale: moment.locale(),
				widgetParent: $(this).parent().css('position', 'relative'),
				widgetPositioning: { vertical: 'bottom' },
				icons: {
					time: 'far fa-clock',
					date: 'far fa-calendar',
					up: 'fas fa-chevron-up',
					down: 'fas fa-chevron-down',
					previous: 'fas fa-chevron-left',
					next: 'fas fa-chevron-right'
				}
			});
			$(this).on('dp.change', function(){
				$(this).trigger('change');
			});
		});

    $('.hijri input', $tbody).calendarsPicker({calendar: $.calendars.instance('islamic', 'ar')});
	},

	saveChanges: function() {
		var changeManager = this.page.changeManager;
		var baseId = this.page.meta.baseId;
		var self = this;

		this.page.getEl('field_errors').hide();
		this.display.find('[data-prop-id]').each(function() {
			var prop = changeManager.getPropertyManager($(this).data('prop-id'));
			prop.setValue($(this).val());

			changeManager.addChange(prop);
		});

		var customFieldData = this.display.find('.custom-field input, .custom-field textarea, .custom-field select').serializeArray();
		for (var i = 0; i < customFieldData.length; i++) {
			customFieldData[i].name = customFieldData[i].name.replace(baseId + '_', '');
		}
		customFieldData.unshift({name: 'custom_fields[]', value: ''});
    customFieldData = self.normalizeCustomFieldValues(customFieldData);

		this.$scope.is_saving = true;

		changeManager.saveChanges(
			customFieldData,
			(function(data) {
        self.$scope.is_saving = false;
				if (data.data && data.data.reload) {
					this.page.closeSelf();
					DeskPRO_Window.runPageRoute('ticket:' + BASE_URL + 'agent/tickets/' + this.page.meta.ticket_id);
				} else {
          setTimeout(function() {
          	self.initFileCustomFields();
          }, 1);
        }
			}).bind(this),
			(function(xhr, code, message) {
        self.$scope.is_saving = false;
        // this.closeEditMode();
        var div = $('<div><strong>Server error: </strong>' + message + '</div>');
        DeskPRO_Window.showAlert(div);
				console.error(message);
			}).bind(this),
			function(data) {
        self.$scope.is_saving = false;
				if (!data.fields) return;
				self.$scope.$apply(function(){
					for (var i = 0; i < data.fields.length; i++) {
						self.$scope.editField(data.fields[i]);
					}
				});
			}
		);
	},

	replaceHolders: function(html) {
		var labels = this.display.find('tbody.labels-row');
		var last = this.display.find('tbody.controls-row');
		$('select', this.display).select2('close');
		this.$scope.$destroy();

		var old = this.display;
		var newDisplay = $('<table cellspacing="0" cellpadding="0" width="100%" class="field-holders-table mode-edit-on">' + html + '</table>');
		newDisplay.append(this.display.find('.hidden-row'));
		newDisplay.append(this.display.find('.controls-row'));
		this.page.rewriteRadioNames(newDisplay);
		this.display = newDisplay;
    this.display.prepend(labels);
    this.display.append(last);

    old.after(this.display);
    old.remove();

		this.initScope(this.page.getEl('field_holders'));

    this.lastDepId = null;
		this.updateDisplayNow();
		this.$scope.$apply();
	},

  initDateCustomFields: function() {
    var self = this;
    
    self.display.find('.Date.customfield input').each(function(){
      if ($(this).val()) {
        $(this).val(self.convertDateFormat('YYYY-MM-DD', 'L', $(this).val()));
      }
      var parent = $(this).closest('.custom-field.item');
      if (parent.data('default-value')) {
        parent.data('default-value', self.convertDateFormat('YYYY-MM-DD', 'L', parent.data('default-value')));
      }
    });
    self.display.find('.DateTime.customfield input').each(function(){
      if ($(this).val()) {
        $(this).val(self.convertDateFormat('YYYY-MM-DD HH:mm', 'L HH:mm', $(this).val()));
      }
      var parent = $(this).closest('.custom-field.item');
      if (parent.data('default-value')) {
        parent.data('default-value', self.convertDateFormat('YYYY-MM-DD HH:mm', 'L HH:mm', parent.data('default-value')));
      }
    });
  },

  initFileCustomFields: function() {
    this.customFieldsUpload = new DeskPRO.Agent.PageHelper.CustomFieldUpload(this.display);

    var self = this;
    self.display.find('.File.customfield input[type="hidden"]').each(function() {
			var $el = $(this);

			if (!self.display.find('.edit-wrapper[data-blob-id='+$el.val()+']').length) {
        var $removeBtn = $('<em class="remove-attach-trigger"></em>');
        var $editWrapper = $('<div data-blob-id="'+$el.val()+'" class="edit-wrapper" />');
        var $fileLink = self.display.find('[data-blob-id='+$el.val()+']').clone();

        $editWrapper.append($('<label>'+$('<div />').append($fileLink).html()+'</label>'));
        $editWrapper.append($removeBtn);
        $editWrapper.insertAfter($el);

        $removeBtn.on('click', function() {
          $el.remove();
          $editWrapper.remove();

          self.customFieldsUpload.updateVisibility();
        });
			}
		});
  },
  
  normalizeCustomFieldValues: function(customFieldsData) {
    var self = this;
    var nameToHandlerMap = {};
    // for now we need only Date and DateTime fields
    self.display.find('.custom-field input').each(function(){
      // skip `hijri` now
      if ($(this).closest('.form.customfield.hijri').length) {
        return;
      }
      nameToHandlerMap[$(this).attr('name')] = $(this).closest('.custom-field.item').data('custom-field-handler');
    });

    return customFieldsData.map(function(customField){
      if (nameToHandlerMap[customField.name] === 'date') {
        customField.value = self.convertDateFormat('L', 'YYYY-MM-DD', customField.value);
      } else if (nameToHandlerMap[customField.name] === 'datetime') {
        customField.value = self.convertDateFormat('L HH:mm', 'YYYY-MM-DD HH:mm', customField.value);
      }

      return customField;
    });
  },

  convertDateFormat: function(from, to, value){
    if (!value) {
      return value;
    }
    var mom = moment(value, from);
    return mom.isValid() ? mom.format(to) : value;
  },

	destroy: function() {
		this.page = null;
    this.$scope && this.$scope.$destroy();
    this.$scope = null;
    this.display = null;
	}
});
