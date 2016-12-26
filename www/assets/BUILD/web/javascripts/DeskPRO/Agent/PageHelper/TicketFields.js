Orb.createNamespace('DeskPRO.Agent.PageHelper');

/**
 * Handles updating display based on department and rules
 */
DeskPRO.Agent.PageHelper.TicketFields = new Orb.Class({
	initialize: function(page) {
		var self = this;
		this.page = page;
		this.display = this.page.getEl('field_holders').find('.field-holders-table');
		this.initFieldWidgets();

		this.mode = 'view';

		this.initScope(this.page.getEl('field_holders'));
		this.no_value_fields = [];

		this.ticketReader = {
			getDepartmentId: function() {
				var catId = self.page.getEl('department_id').val();
				return parseInt(catId) || 0;
			},
			getCategoryId: function() {
				var catId = self.display.find('select.prop-input-category_id:first').val();
				return parseInt(catId) || 0;
			},
			getPriorityId: function() {
				var catId = self.display.find('select.prop-input-priority_id:first').val();
				return parseInt(catId) || 0;
			},
			getProductId: function() {
				var catId = self.display.find('select.prop-input-product:first').val();
				return parseInt(catId) || 0;
			},
			getOrganizationId: function() {
				return 0;
			},
			getWorkflowId: function() {
				var catId = self.display.find('select.prop-input-workflow_id:first').val();
				return parseInt(catId) || 0;
			},
			getFieldValue: function(name) {
				var $holders = self.page.getEl('field_holders');
				var $field = $('[name="' + name + '"]', $holders);
				if ($field.is(':checkbox')) {
					return $field.is(':checked');
				}
				if ($field.is('input:not(:radio, :checkbox), textarea')) {
					return $field.val();
				}
				$field = $('[name="' + name + '"], [name="' + name + '[]"]', $holders);
				if ($field.hasClass('with-select2')) {
					var val = $.trim($field.select2('val'));
					return $.trim(val) ? val : null;
				}
				return $field.filter(':checked').map(function(i, el) { return el.value; }).get();
			},
			getTicketFieldValue: function(fieldId) {
				return this.getFieldValue('custom_fields[field_' + fieldId + ']');
			},
			getUserFieldValue: function(fieldId) {
				return this.getFieldValue('custom_person_fields[field_' + fieldId + ']');
			},
			getOrgFieldValue: function(fieldId) {
				return this.getFieldValue('custom_org_fields[field_' + fieldId + ']');
			}
		};

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
				}
			}
		});
	},

	initScope: function(el) {
		var self = this;
		var $scope = this.$scope = DeskPRO_Window.$scope.$new();

		DeskPRO_Window.ngModule.dpInjector.invoke(['$compile', function($compile) {
			el.data('$ngControllerController', self);
			$compile(el.contents())(self.$scope);
		}]);

		$scope.edit_fields = [];
		$scope.fields = {};
		$scope.editables = {
			language: 1
		};
		$scope.show_hidden = 0;

		$scope.editField = function(field) {
			if (!$scope.editables[field]) return;
			if ($scope.isEditMode(field)) return;
			$scope.edit_fields.push(field);
		};

		$scope.isEditMode = function(field) {
			return $scope.edit_fields.indexOf(field) !== -1;
		};

		$scope.cancelEdit = function() {
			$scope.edit_fields.length = 0;
		};

		$scope.saveFields = function() {
			self.saveChanges();
			$scope.edit_fields.length = 0;
		};

		$scope.showHidden = function() {
			$scope.show_hidden = 1;
			for (var i = 0; i < self.no_value_fields.length; i++) {
				$scope.editField(self.no_value_fields[i]);
			}
			self.updateDisplay();
		};
	},

	updateDisplay: function() {
		var fields = [], reader = this.ticketReader;
		var $scope = this.$scope;
		if (window.DESKPRO_TICKET_DISPLAY) {
			fields = window.DESKPRO_TICKET_DISPLAY.getLayout(reader.getDepartmentId()).getFields();
		}

		var isVisible = function(f) {
			var visible = f.isVisibleOnView || f.isVisibleOnViewAlways;
			return f.checkFn ? visible && f.checkFn(reader) : visible;
		};

		$scope.hidden = 0;
		$scope.editables = {};
		this.no_value_fields = [];
		var $ctrls = this.display.find('.controls-row');
		$ctrls.removeClass('off').prev().removeClass('off');

		for (var i = 0; i < fields.length; i++) {
			var f = fields[i];

			var row = this.display.find('.item.' + f.id);
			if (undefined === $scope.fields[f.id]) {
				row.detach().removeClass('off').insertBefore($ctrls);
			}
			var noValue = row.hasClass('no-value');

			if (f.isVisibleOnEdit) {
				$scope.editables[f.id] = 1;
			}

			var show = isVisible(f) && (f.isVisibleOnViewAlways || !noValue || $scope.show_hidden);
			$scope.fields[f.id] = !!show;

			if (!f.isVisibleOnViewAlways && noValue) {
				this.no_value_fields.push(f.id);
			}
		}

		$scope.hidden = this.no_value_fields.length;
	},

	initFieldWidgets: function() {
		this.display.find('select').not('.no-dp-select').dpMultiLevelSelect();
		DP.select(this.display.find('select'));
		$('.Date.customfield input', this.display).each(function(){
			$(this).datetimepicker({
				format: 'YYYY-MM-DD',
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

		$('.DateTime.customfield input', this.display).each(function(){
			$(this).datetimepicker({
				format: 'YYYY-MM-DD HH:mm',
				widgetParent: $(this).parent().css('position', 'relative'),
				widgetPositioning: { vertical: 'bottom' },
				icons: {
					time: 'fa fa-clock-o',
					date: 'fa fa-calendar-o',
					up: 'fa fa-chevron-up',
					down: 'fa fa-chevron-down',
					previous: 'fa fa-chevron-left',
					next: 'fa fa-chevron-right'
				}
			});
			$(this).on('dp.change', function(){

				$(this).trigger('change');
			});
		});
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

		changeManager.saveChanges(
			customFieldData,
			(function(data) {
				if (data.data && data.data.reload) {
					this.page.closeSelf();
					DeskPRO_Window.runPageRoute('ticket:' + BASE_URL + 'agent/tickets/' + this.page.meta.ticket_id);
				}
			}).bind(this),
			(function(xhr, code, message) {
        // this.closeEditMode();
        var div = $('<div><strong>Server error: </strong>' + message + '</div>');
        DeskPRO_Window.showAlert(div);
				console.error(message);
			}).bind(this),
			function(data) {
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
		this.$scope.$destroy();

		var old = this.display;
		var newDisplay = $('<table cellspacing="0" cellpadding="0" width="100%" class="field-holders-table mode-edit-on">' + html + '</table>');
		this.page.rewriteRadioNames(newDisplay);
		this.display = newDisplay;
    this.display.prepend(labels);
    this.display.append(last);

    old.after(this.display);
    old.remove();

		this.initFieldWidgets();

		this.initScope(this.page.getEl('field_holders'));

		this.currentDisplay = [];
		this.currentDisplayModify = [];
		this.updateDisplay();
		this.$scope.$apply();
	}
});
