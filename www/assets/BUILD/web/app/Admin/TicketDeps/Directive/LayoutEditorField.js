/*
 * decaffeinate suggestions:
 * DS101: Remove unnecessary use of Array.from
 * DS102: Remove unnecessary code created because of implicit returns
 * DS203: Remove `|| {}` from converted for-own loops
 * DS207: Consider shorter variations of null checks
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(['DeskPRO/Util/Util'], function(Util) {
  class LayoutEditorField {
    constructor(scope, element, attrs, ngModel, $modal, dpObTypesDefTicketCriteria, TicketFields, UserFields, $q, $timeout, TicketFieldsPerPerson, TicketFieldsPerOrg, OrgFields) {

      this.scope = scope;
      this.element = element;
      this.attrs = attrs;
      this.ngModel = ngModel;
      this.$modal = $modal;
      this.dpObTypesDefTicketCriteria = dpObTypesDefTicketCriteria;
      this.scope.ticketFieldTitleFilter = f => {
        return (this.scope.field.field_type === 'ticket_field') && ((f.id+'') === (this.scope.field.field_id+''));
      };
      this.scope.userFieldTitleFilter = f => {
        return (this.scope.field.field_type === 'user_field') && ((f.id+'') === (this.scope.field.field_id+''));
      };
      this.scope.orgFieldTitleFilter = f => {
        return (this.scope.field.field_type === 'org_field') && ((f.id+'') === (this.scope.field.field_id+''));
      };
      this.scope.CustomFieldTitleFilter = f => {
        return (this.scope.field.field_type === 'custom_field') && ((f.id+'') === (this.scope.field.field_id+''));
      };

      $q.all([
        TicketFields.loadList(),
        UserFields.loadList(),
        TicketFieldsPerPerson.all(),
        TicketFieldsPerOrg.all(),
        OrgFields.loadList(),
        this.dpObTypesDefTicketCriteria.loadDataOptions()
      ]).then(results => {
        this.scope.custom_ticket_fields     = results[0];
        this.scope.custom_user_fields       = results[1];
        this.scope.ticket_fields_per_person = results[2];
        this.scope.ticket_fields_per_org    = results[3];
        this.scope.custom_org_fields        = results[4];

        return $timeout(() => {
          return this._initEvents();
        }
        , 1);
      });
    }


    _initEvents() {
      if (!this.scope.isSticky) {
        this.element.find('.opt_btn').on('click', ev => {
          ev.preventDefault();
          return this.openOptions();
        });

        return this.element.find('.remove_btn').on('click', ev => {
          ev.preventDefault();
          if (this.scope.removeRow != null) {
            return this.scope.removeRow();
          } else {
            return this.scope.$destroy();
          }
        });
      } else {
        return this.element.find('nav').remove();
      }
    }

    openOptions() {
      let inst, tpl;
      if (this.scope.type === 'user') {
        tpl = 'ticketdeps_layouteditor_user_options';
      } else {
        tpl = 'ticketdeps_layouteditor_agent_options';
      }

      if (!this.scope.field.options) {
        this.scope.field.options = {};
      }

      const { field } = this.scope;

      return inst = this.$modal.open({
        templateUrl: tpl,
        controller: ['$scope', '$modalInstance', 'options', 'typeDef', 'dpObTypesDefTicketCriteria', function($scope, $modalInstance, options, typeDef, types) {
          let terms;
          if ((options.criteria == null)) { options.criteria = {}; }
          if (!(options.criteria != null ? options.criteria.terms : undefined)) { options.criteria.terms = {}; }
          if (!(options.criteria != null ? options.criteria.mode : undefined)) { options.criteria.mode = 'all'; }

          $scope.formOptions = {
            with_criteria: false
          };

          if (Util.isArray(options.criteria.terms)) {
            terms = {};
            for (let t of Array.from(options.criteria.terms)) {
              terms[Util.uid('t')] = t;
            }
            options.criteria.terms = terms;
          }

          for (let _x of Object.keys(options.criteria.terms || {})) {
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

          const initFieldGetter = function(base_name, f) {
            options = {};
            options.type_name = f.type_name;
            if (f.type_name === 'choice') {
              options.operators = ['isset', 'not_isset', 'is', 'not'];
            } else if (f.type_name === 'toggle') {
              options.operators = ['isset', 'not_isset'];
            } else if ((f.type_name === 'date') || (f.type_name === 'datetime')) {
              options.operators = ['isset', 'not_isset', 'lte', 'gte', 'between'];
            } else {
              options.operators = ['isset', 'not_isset', 'is', 'not', 'contains', 'notcontains', 'is_regex', 'not_regex'];
            }
            return types.initFieldGetter(base_name, f, true, options);
          };

          types.loadDataOptions().then(() => {
            let f, sub_options;
            if (types.options_data != null ? types.options_data.ticket_fields : undefined) {
              sub_options = [];

              for (f of Array.from(types.options_data.ticket_fields)) {
                sub_options.push({
                  title: f.title,
                  value: initFieldGetter('CheckTicketField', f)
                });
              }
            }

            if (types.options_data != null ? types.options_data.contextual_fields : undefined) {
              for (f of Array.from(types.options_data.contextual_fields)) {
                sub_options.push({
                  title: f.title,
                  value: initFieldGetter('CheckTicketContextualField', f)
                });
              }

              if (sub_options.length) {
                $scope.criteriaOptions.push({
                  title: 'Ticket Fields',
                  subOptions: sub_options
                });
              }
            }

            if (types.options_data != null ? types.options_data.user_fields : undefined) {
              sub_options = [];

              for (f of Array.from(types.options_data.user_fields)) {
                sub_options.push({
                  title: f.title,
                  value: initFieldGetter('CheckUserField', f)
                });
              }

              if (sub_options.length) {
                $scope.criteriaOptions.push({
                  title: 'Person Fields',
                  subOptions: sub_options
                });
              }
            }

            if (types.options_data != null ? types.options_data.org_fields : undefined) {
              sub_options = [];

              for (f of Array.from(types.options_data.org_fields)) {
                sub_options.push({
                  title: f.title,
                  value: initFieldGetter('CheckOrgField', f)
                });
              }

              if (sub_options.length) {
                return $scope.criteriaOptions.push({
                  title: 'Organization Fields',
                  subOptions: sub_options
                });
              }
            }
          });

          $scope.criteriaTypesDef = typeDef;

          $scope.dismiss = () => $modalInstance.dismiss();

          return $scope.done = function() {
            if (!$scope.formOptions.with_criteria) {
              $scope.options.criteria.terms = {};
            }

            return $modalInstance.dismiss();
          };
        }
        ],
        resolve: {
          options: () => {
            return this.scope.field.options;
          },

          typeDef: () => {
            return this.dpObTypesDefTicketCriteria;
          }
        }
      });
    }
  }

  return [ '$modal', 'dpObTypesDefTicketCriteria', 'DataService', '$q', '$timeout', function($modal, dpObTypesDefTicketCriteria, DataService, $q, $timeout) {
    const directive = {};
    directive.restrict    = 'E';
    directive.replace     = true;
    directive.templateUrl = "TicketDeps/layout-editor-field.html";

    const TicketFields          = DataService.get('TicketFields');
    const UserFields            = DataService.get('UserFields');
    const OrgFields             = DataService.get('OrgFields');
    const TicketFieldsPerPerson = DataService.get('CustomFields', 'ticket', 'person');
    const TicketFieldsPerOrg    = DataService.get('CustomFields', 'ticket', 'organization');

    directive.link = function(scope, element, attrs, ngModel) {
      let handler;
      if (!scope.field.options) {                 scope.field.options = {}; }
      if ((scope.field.options.criteria == null)) {       scope.field.options.criteria = {}; }
      if (!(scope.field.options.criteria != null ? scope.field.options.criteria.terms : undefined)) { scope.field.options.criteria.terms = {}; }
      if (!(scope.field.options.criteria != null ? scope.field.options.criteria.mode : undefined)) {  scope.field.options.criteria.mode = 'all'; }

      return handler = new LayoutEditorField(
        scope,
        element,
        attrs,
        ngModel,
        $modal,
        dpObTypesDefTicketCriteria,
        TicketFields,
        UserFields,
        $q,
        $timeout,
        TicketFieldsPerPerson,
        TicketFieldsPerOrg,
        OrgFields
      );
    };

    return directive;
  }
  ];
});
