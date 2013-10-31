(function() {
  define(function() {
    var LayoutEditorField;
    LayoutEditorField = (function() {
      function LayoutEditorField(scope, element, attrs, ngModel, $modal, dpObTypesDefTicketCriteria) {
        this.scope = scope;
        this.element = element;
        this.attrs = attrs;
        this.ngModel = ngModel;
        this.$modal = $modal;
        this.dpObTypesDefTicketCriteria = dpObTypesDefTicketCriteria;
        this._initEvents();
      }

      LayoutEditorField.prototype._initEvents = function() {
        var _this = this;
        if (!this.scope.isSticky) {
          this.element.find('.opt_btn').on('click', function(ev) {
            ev.preventDefault();
            return _this.openOptions();
          });
          return this.element.find('.remove_btn').on('click', function(ev) {
            ev.preventDefault();
            if (_this.scope.removeRow != null) {
              return _this.scope.removeRow();
            } else {
              return _this.scope.$destroy();
            }
          });
        } else {
          return this.element.find('nav').remove();
        }
      };

      LayoutEditorField.prototype.openOptions = function() {
        var inst, tpl,
          _this = this;
        console.log("oepn");
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
              $scope.options = options;
              $scope.with_criteria = false;
              if ($scope.options.criteria.terms.length) {
                $scope.with_criteria = true;
              }
              $scope.criteriaOptions = [];
              $scope.criteriaOptions.push({
                title: 'Department',
                value: 'department'
              });
              $scope.criteriaOptions.push({
                title: 'Product',
                value: 'product'
              });
              $scope.criteriaOptions.push({
                title: 'Category',
                value: 'category'
              });
              $scope.criteriaOptions.push({
                title: 'Priority',
                value: 'priority'
              });
              $scope.criteriaOptions.push({
                title: 'Workflow',
                value: 'workflow'
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
            options: function() {
              return _this.scope.field.options;
            },
            typeDef: function() {
              return _this.dpObTypesDefTicketCriteria;
            }
          }
        });
      };

      return LayoutEditorField;

    })();
    return [
      '$modal', 'dpObTypesDefTicketCriteria', function($modal, dpObTypesDefTicketCriteria) {
        var directive;
        directive = {};
        directive.restrict = 'E';
        directive.replace = true;
        directive.templateUrl = "TicketDeps/layout-editor-field.html";
        directive.link = function(scope, element, attrs, ngModel) {
          var handler;
          return handler = new LayoutEditorField(scope, element, attrs, ngModel, $modal, dpObTypesDefTicketCriteria);
        };
        return directive;
      }
    ];
  });

}).call(this);

/*
//@ sourceMappingURL=LayoutEditorField.js.map
*/