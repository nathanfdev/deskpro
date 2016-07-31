Orb.createNamespace('DeskPRO.Agent.PageHelper');

/**
 * Handles updating display based on department and rules
 */
DeskPRO.Agent.PageHelper.TicketFields = new Orb.Class({
	initialize: function(page) {
		var self = this;
		this.page = page;
		this.display = this.page.getEl('field_holders').find('.field-holders-table');

		this.mode = 'view';
		this.currentDisplay = [];
		this.currentDisplayModify = [];

		this.initScope(this.page.getEl('field_holders'));

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
			}
		};

		this.fieldDisplay = new DeskPRO.Agent.PageHelper.TicketFieldDisplay(this.ticketReader, 'view');
		this.fieldDisplayModify = new DeskPRO.Agent.PageHelper.TicketFieldDisplay(this.ticketReader, 'modify');

		this.page.getEl('department').on('change', function() {
			self.updateDisplay();
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
		$scope.editables = {
			language: 1
		};

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
	},

	updateDisplay: function() {
		return this.updateDisplayNew();
	},

	updateDisplayNew: function() {
		var fields = [], reader = this.ticketReader;
		var $scope = this.$scope;
		if (window.DESKPRO_TICKET_DISPLAY) {
			fields = window.DESKPRO_TICKET_DISPLAY.getLayout(reader.getDepartmentId()).getFields();
		}

		var isVisible = function(f) {
			var visible = f.isVisibleOnView || f.isVisibleOnViewAlways;
			return f.checkFn ? visible && f.checkFn(reader) : visible;
		};

		var change = false;

		// Check to see if the fields are the same and in the same order
		if (fields.length === this.currentDisplay.length) {
			var cd = this.currentDisplay;
			for (var i = 0; i < fields.length; i++) {
				var field = fields[i];

				field.visible = isVisible(field);

				if (field.id !== cd[i].id || field.visible !== cd[i].visible) {
					change = true;
					break;
				}
			}
		} else {
			change = true;
		}

		// No Changes, dont need to do any expensive dom work
		if (!change) {
			console.log("[TicketFields] No change");
		}

		// still need to run through to make sure visibility on
		// rows is set

		this.currentDisplay = fields;

		this.display.find('tbody.item.item-on').hide().removeClass('item-on');
		var labels = this.display.find('tbody.labels-row');
		var last = this.display.find('tbody.controls-row');

		for (i = 0; i < this.currentDisplay.length; i++) {
			var f = this.currentDisplay[i];

			if (!isVisible(f)) {
				continue;
			}

			if (f.isVisibleOnEdit) {
				$scope.editables[f.id] = 1;
			}

			var row = this.display.find('.item.' + f.id);
			row.detach().insertBefore(last).show().addClass('item-on');
		}
		this.initFieldWidgets();

		console.info(fields);
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
			$(this).on('dp.change', function(){
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
			}).bind(this)
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

		old.after(this.display);
		old.remove();
		this.display.prepend(labels);
		this.display.append(last);

		this.initScope(this.page.getEl('field_holders'));
		this.$scope.$apply();

		this.currentDisplay = [];
		this.currentDisplayModify = [];
		this.updateDisplay();
	}
});
