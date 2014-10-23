(function() {
  var __hasProp = {}.hasOwnProperty;

  define(['DeskPRO/Util/Util'], function(Util) {
    var LayoutEditorField;
    LayoutEditorField = (function() {
      function LayoutEditorField(scope, element, attrs, ngModel, $modal, dpObTypesDefTicketCriteria, TicketFields, UserFields, $q, $timeout, TicketFieldsPerPerson, TicketFieldsPerOrg) {
        this.scope = scope;
        this.element = element;
        this.attrs = attrs;
        this.ngModel = ngModel;
        this.$modal = $modal;
        this.dpObTypesDefTicketCriteria = dpObTypesDefTicketCriteria;
        this.scope.ticketFieldTitleFilter = (function(_this) {
          return function(f) {
            return _this.scope.field.field_type === 'ticket_field' && (f.id + '') === (_this.scope.field.field_id + '');
          };
        })(this);
        this.scope.userFieldTitleFilter = (function(_this) {
          return function(f) {
            return _this.scope.field.field_type === 'user_field' && (f.id + '') === (_this.scope.field.field_id + '');
          };
        })(this);
        this.scope.CustomFieldTitleFilter = (function(_this) {
          return function(f) {
            return _this.scope.field.field_type === 'custom_field' && (f.id + '') === (_this.scope.field.field_id + '');
          };
        })(this);
        $q.all([TicketFields.loadList(), UserFields.loadList(), TicketFieldsPerPerson.all(), TicketFieldsPerOrg.all()]).then((function(_this) {
          return function(results) {
            _this.scope.custom_ticket_fields = results[0];
            _this.scope.custom_user_fields = results[1];
            _this.scope.ticket_fields_per_person = results[2];
            _this.scope.ticket_fields_per_org = results[3];
            return $timeout(function() {
              return _this._initEvents();
            }, 1);
          };
        })(this));
      }

      LayoutEditorField.prototype._initEvents = function() {
        if (!this.scope.isSticky) {
          this.element.find('.opt_btn').on('click', (function(_this) {
            return function(ev) {
              ev.preventDefault();
              return _this.openOptions();
            };
          })(this));
          return this.element.find('.remove_btn').on('click', (function(_this) {
            return function(ev) {
              ev.preventDefault();
              if (_this.scope.removeRow != null) {
                return _this.scope.removeRow();
              } else {
                return _this.scope.$destroy();
              }
            };
          })(this));
        } else {
          return this.element.find('nav').remove();
        }
      };

      LayoutEditorField.prototype.openOptions = function() {
        var field, inst, tpl;
        if (this.scope.type === 'user') {
          tpl = 'ticketdeps_layouteditor_user_options';
        } else {
          tpl = 'ticketdeps_layouteditor_agent_options';
        }
        if (!this.scope.field.options) {
          this.scope.field.options = {};
        }
        field = this.scope.field;
        return inst = this.$modal.open({
          templateUrl: tpl,
          controller: [
            '$scope', '$modalInstance', 'options', 'typeDef', function($scope, $modalInstance, options, typeDef) {
              var t, terms, _, _i, _len, _ref, _ref1, _ref2, _ref3;
              if (options.criteria == null) {
                options.criteria = {};
              }
              if (!((_ref = options.criteria) != null ? _ref.terms : void 0)) {
                options.criteria.terms = {};
              }
              if (!((_ref1 = options.criteria) != null ? _ref1.mode : void 0)) {
                options.criteria.mode = 'all';
              }
              $scope.formOptions = {
                with_criteria: false
              };
              if (Util.isArray(options.criteria.terms)) {
                terms = {};
                _ref2 = options.criteria.terms;
                for (_i = 0, _len = _ref2.length; _i < _len; _i++) {
                  t = _ref2[_i];
                  terms[Util.uid('t')] = t;
                }
                options.criteria.terms = terms;
              }
              _ref3 = options.criteria.terms;
              for (_ in _ref3) {
                if (!__hasProp.call(_ref3, _)) continue;
                $scope.formOptions.with_criteria = true;
                break;
              }
              $scope.options = options;
              $scope.criteriaOptions = [];
              $scope.criteriaOptions.push({
                title: 'Department',
                value: 'CheckDepartment'
              });
              $scope.criteriaOptions.push({
                title: 'Product',
                value: 'CheckProduct'
              });
              $scope.criteriaOptions.push({
                title: 'Category',
                value: 'CheckCategory'
              });
              $scope.criteriaOptions.push({
                title: 'Priority',
                value: 'CheckPriority'
              });
              $scope.criteriaOptions.push({
                title: 'Workflow',
                value: 'CheckWorkflow'
              });
              $scope.criteriaTypesDef = typeDef;
              $scope.dismiss = function() {
                return $modalInstance.dismiss();
              };
              return $scope.done = function() {
                if (!$scope.formOptions.with_criteria) {
                  $scope.options.criteria.terms = {};
                }
                return $modalInstance.dismiss();
              };
            }
          ],
          resolve: {
            options: (function(_this) {
              return function() {
                return _this.scope.field.options;
              };
            })(this),
            typeDef: (function(_this) {
              return function() {
                return _this.dpObTypesDefTicketCriteria;
              };
            })(this)
          }
        });
      };

      return LayoutEditorField;

    })();
    return [
      '$modal', 'dpObTypesDefTicketCriteria', 'DataService', '$q', '$timeout', function($modal, dpObTypesDefTicketCriteria, DataService, $q, $timeout) {
        var TicketFields, TicketFieldsPerOrg, TicketFieldsPerPerson, UserFields, directive;
        directive = {};
        directive.restrict = 'E';
        directive.replace = true;
        directive.templateUrl = "TicketDeps/layout-editor-field.html";
        TicketFields = DataService.get('TicketFields');
        UserFields = DataService.get('UserFields');
        TicketFieldsPerPerson = DataService.get('CustomFields', 'ticket', 'person');
        TicketFieldsPerOrg = DataService.get('CustomFields', 'ticket', 'organization');
        directive.link = function(scope, element, attrs, ngModel) {
          var handler, _ref, _ref1;
          if (!scope.field.options) {
            scope.field.options = {};
          }
          if (scope.field.options.criteria == null) {
            scope.field.options.criteria = {};
          }
          if (!((_ref = scope.field.options.criteria) != null ? _ref.terms : void 0)) {
            scope.field.options.criteria.terms = {};
          }
          if (!((_ref1 = scope.field.options.criteria) != null ? _ref1.mode : void 0)) {
            scope.field.options.criteria.mode = 'all';
          }
          return handler = new LayoutEditorField(scope, element, attrs, ngModel, $modal, dpObTypesDefTicketCriteria, TicketFields, UserFields, $q, $timeout, TicketFieldsPerPerson, TicketFieldsPerOrg);
        };
        return directive;
      }
    ];
  });

}).call(this);

//# sourceMappingURL=LayoutEditorField.js.map
