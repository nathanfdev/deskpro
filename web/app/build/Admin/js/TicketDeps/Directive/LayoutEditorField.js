(function() {
  define(function() {
    var LayoutEditorField;
    LayoutEditorField = (function() {
      function LayoutEditorField(scope, element, attrs, ngModel, $modal, dpObTypesDefTicketCriteria, TicketFields, UserFields, $q, $timeout) {
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
        $q.all([TicketFields.loadList(), UserFields.loadList()]).then((function(_this) {
          return function(results) {
            var tFields, uFields;
            tFields = results[0];
            uFields = results[1];
            _this.scope.custom_ticket_fields = tFields;
            _this.scope.custom_user_fields = uFields;
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
        var inst, tpl;
        if (this.scope.type === 'user') {
          tpl = 'ticketdeps_layouteditor_user_options';
        } else {
          tpl = 'ticketdeps_layouteditor_agent_options';
        }
        if (!this.scope.field.options) {
          this.scope.field.options = {};
        }
        return inst = this.$modal.open({
          templateUrl: tpl,
          controller: [
            '$scope', '$modalInstance', 'options', 'typeDef', function($scope, $modalInstance, options, typeDef) {
              var _ref, _ref1;
              if (options.criteria == null) {
                options.criteria = {};
              }
              if (!((_ref = options.criteria) != null ? _ref.terms : void 0)) {
                options.criteria.terms = {};
              }
              if (!((_ref1 = options.criteria) != null ? _ref1.mode : void 0)) {
                options.criteria.mode = 'all';
              }
              $scope.options = options;
              $scope.with_criteria = false;
              if ($scope.options.criteria.terms.length) {
                $scope.with_criteria = true;
              }
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
              return $scope.done = function() {
                if (!$scope.with_criteria) {
                  $scope.options.criteria.terms.length = 0;
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
        var TicketFields, UserFields, directive;
        directive = {};
        directive.restrict = 'E';
        directive.replace = true;
        directive.templateUrl = "TicketDeps/layout-editor-field.html";
        TicketFields = DataService.get('TicketFields');
        UserFields = DataService.get('UserFields');
        directive.link = function(scope, element, attrs, ngModel) {
          var handler;
          return handler = new LayoutEditorField(scope, element, attrs, ngModel, $modal, dpObTypesDefTicketCriteria, TicketFields, UserFields, $q, $timeout);
        };
        return directive;
      }
    ];
  });

}).call(this);

//# sourceMappingURL=LayoutEditorField.js.map
