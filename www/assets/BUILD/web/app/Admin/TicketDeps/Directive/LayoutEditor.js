// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS101: Remove unnecessary use of Array.from
 * DS102: Remove unnecessary code created because of implicit returns
 * DS205: Consider reworking code to avoid use of IIFEs
 * DS207: Consider shorter variations of null checks
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define([
  'angular',
  'DeskPRO/Util/Arrays'
], function(
  angular,
  Arrays
) {
  class InterfaceHandler {
    constructor(scope, element, attr, ngModel, $compile, TicketFields, UserFields, $q, $timeout, logger, TicketFieldsPerPerson, TicketFieldsPerOrg, OrgFields) {
      this.scope    = scope;
      this.element  = element;
      this.ngModel  = ngModel;
      this.$compile = $compile;
      this.logger   = logger;

      this.scope.form_tab = 'user';

      this.els = {};
      this.els.user_tab        = this.element.find('.user-form');
      this.els.user_worksheet  = this.els.user_tab.find('.form-worksheet');
      this.els.agent_tab       = this.element.find('.agent-form');
      this.els.agent_worksheet = this.els.agent_tab.find('.form-worksheet');

      this.required_fields = {
        user: [],
        agent: []
      };

      this.ngModel.$formatters.push( modelValue => {
        // Make sure the basic data structure exists
        let f, has, i;
        if (!modelValue) {
          modelValue = {};
        }
        if ((modelValue.user == null)) {
          modelValue.user = [];
        }
        if ((modelValue.agent == null)) {
          modelValue.agent = [];
        }

        for (var fieldType of Array.from(this.required_fields.user)) {
          has = false;
          for (f of Array.from(modelValue.user)) {
            if (!f.id) {
              if (f.field_id) {
                f.id = `${f.field_type}_${f.field_id}`;
              } else {
                f.id = f.field_type;
              }
            }

            if (f.field_type === fieldType) {
              has = true;
            }
          }

          if (!has) {
            modelValue.user.push(this.createFieldValue(fieldType));
          }
        }

        for (fieldType of Array.from(this.required_fields.agent)) {
          has = false;
          for (f of Array.from(modelValue.agent)) {
            if (!f.id) {
              if (f.field_id) {
                f.id = `${f.field_type}_${f.field_id}`;
              } else {
                f.id = f.field_type;
              }
            }

            if (f.field_type === fieldType) {
              has = true;
            }
          }
          if (!has) {
            modelValue.agent.push(this.createFieldValue(fieldType));
          }
        }

        for (i = 0; i < modelValue.user.length; i++) {
          f = modelValue.user[i];
          f.display_order = i;
        }
        for (i = 0; i < modelValue.agent.length; i++) {
          f = modelValue.agent[i];
          f.display_order = i;
        }

        return modelValue;
      });

      $timeout(() => {
        this._initTab('user', this.els.user_tab);
        return this._initTab('agent', this.els.agent_tab);
      }
      , 1);

      this.ngModel.$parsers.push( viewModel => {
        return viewModel;
      });

      this.ngModel.$render = () => {
        return this.render();
      };

      $q.all([TicketFields.loadList(), UserFields.loadList(), TicketFieldsPerPerson.all(), TicketFieldsPerOrg.all(), OrgFields.loadList()])
      .then( results => {
        this.scope.field_status             = TicketFields.field_enabled;
        this.scope.custom_ticket_fields     = results[0];
        this.scope.custom_user_fields       = results[1];
        this.scope.ticket_fields_per_person = results[2];
        this.scope.ticket_fields_per_org    = results[3];
        this.scope.custom_org_fields        = results[4];

        return $timeout(() => {
          this._reInitTab('user', this.els.user_tab);
          this._reInitTab('agent', this.els.agent_tab);

          return this.render();
        }
        , 1);
      });
    }

    _reInitTab(tabType, tab) {
      // moves disabled items to end of the list
      tab.find('.form-elements').find('li.disabled').not('.done-init').each(function() {
        const el = $(this);
        const parent = el.closest('ul');
        return el.detach().appendTo(parent);
      });
      return tab.find('.form-elements').find('li').not('.disabled').draggable({
        appendTo: 'body',
        helper: 'clone',
        connectToSortable: tab.find('.form-worksheet').find('ul')
      });
    }

    _initTab(tabType, tab) {
      const me = this;
      const { ngModel } = this;

      const requiredFields = this.required_fields[tabType];
      tab.find('.dp-layout-editor-layout-field').filter('[data-is-required]').each(function() {
        return requiredFields.push($(this).data('field-type'));
      });

      // moves disabled items to end of the list
      tab.find('.form-elements').find('li.disabled').each(function() {
        const el = $(this);
        const parent = el.closest('ul');
        return el.detach().appendTo(parent);
      }).addClass('done-init');

      tab.find('.form-elements').find('li').not('.disabled').draggable({
        appendTo: 'body',
        helper: 'clone',
        connectToSortable: tab.find('.form-worksheet').find('ul')
      }).addClass('done-init');

      return tab.find('.form-worksheet').find('ul').sortable({
        items: "> li",
        axis: 'y',
        handle: '.drag_handle',
        stop: (event, ui) => {
          if (ui.item != null ? ui.item.hasClass('dp-layout-editor-layout-field') : undefined) {
            const fieldType = ui.item.data('field-type');
            const fieldId   = ui.item.data('field-id') || null;
            me.logger.debug(`[${tabType}] Dragged ${fieldType}_${fieldId || '0'}`);
            const fieldRow = me.createAndAddField(
              tabType,
              fieldType,
              fieldId,
              ui.item
            );
            ui.item.remove();

            if (fieldRow) {
              const fid = fieldRow.data('field-id');
              tab.find(`[data-fid=\"${fid}\"]`).hide();
              return this.updateOrder(tabType, tab);
            }
          }
        },

        update: () => {
          return this.updateOrder(tabType, tab);
        }
      });
    }


    updateOrder(tabType, tab) {
      const { ngModel } = this;
      const orderMap = {};
      tab.find('.form-worksheet').find('ul').find('li').each( function(i) {
        const fid = $(this).data('field-id');
        if (fid) { return orderMap[fid] = i; }
      });
      if (ngModel.$modelValue[tabType] && ngModel.$modelValue[tabType].length) {
        return Array.from(ngModel.$modelValue[tabType]).map((f) =>
          (f.display_order = orderMap[f.id] || 0));
      }
    }

    /*
      * Create a new field, add it to the model and also add it to the UI
      *
      * @param {String} tabType
      * @param {String} fieldType
      * @param {Integer} fieldId
      * @param {HTMLElement} insertAfterEl
    */
    createAndAddField(tabType, fieldType, fieldId = null, insertAfterEl = null) {
      let viewValue = this.ngModel.$viewValue;
      if (!viewValue) {
        viewValue = {};
      }
      if (!viewValue[tabType]) {
        viewValue[tabType] = [];
      }

      const field = this.createFieldValue(fieldType, fieldId || null);

      for (let f of Array.from(viewValue[tabType])) {
        // Already has field of this type,
        // so we will ignore this drop
        if (f.id === field.id) {
          this.logger.info(`[${tabType}] {createAndAddField} Already has ${field.id}`);
          return null;
        }
      }

      const row = this.createFieldRow(tabType, field);

      let insertAt = null;
      if (insertAfterEl) {
        insertAt = $(insertAfterEl).parent().find('.layout-field').index(insertAfterEl);
        row.insertAfter(insertAfterEl);
      } else {
        let ul;
        if (tabType === 'user') {
          ul = this.els.user_worksheet.find('ul').first();
        } else {
          ul = this.els.agent_worksheet.find('ul').first();
        }

        ul.append(row);
      }

      if (insertAt === null) {
        viewValue[tabType].push(field);
      } else {
        Arrays.insertAtIndex(viewValue[tabType], field, insertAt);
      }

      this.ngModel.$setViewValue(viewValue);

      return row;
    }


    /*
      * Creates a new field object
      *
      * @return {Object}
      */
    createFieldValue(fieldType, fieldId = null) {
      let id = fieldType;
      if (fieldId) {
        id += `_${fieldId}`;
      }

      const layoutField = {
        id,
        field_type:    fieldType,
        field_id:      fieldId,
        options: {
          on_newticket: true,
          on_viewticket: true,
          on_viewticket_mode: "always",
          on_editticket: true,
          criteria: {
            mode: "all",
            terms: []
          }
        }
      };

      return layoutField;
    }


    /*
      * Renders a new field row
      *
      * @param {Object} field
      * @return {HTMLElement}
      */
    createFieldRow(tabType, field) {
      const fieldScope = this.scope.$new(true);
      fieldScope.field = field;
      fieldScope.type  = tabType;

      const fid = this.getFieldId(field);

      fieldScope.removeRow = () => {
        const viewValue = this.ngModel.$viewValue[tabType];
        for (let idx = 0; idx < viewValue.length; idx++) {
          const f = viewValue[idx];
          if (f === field) {
            viewValue.splice(idx, 1);
            break;
          }
        }

        fieldRow.remove();
        fieldScope.$destroy();

        const tab = this.els[`${tabType}_tab`].find('.form-elements');
        return tab.find(`[data-fid=\"${fid}\"]`).show();
      };

      if (Array.from(this.required_fields[tabType]).includes(fid)) {
        fieldScope.removeRow = function() {
        };
        fieldScope.isSticky = true;
      }

      var fieldRow = this.$compile(`\
<li class="layout-field"><dp-ticket-layout-editor-field type="${tabType}" ng-model="field" /></li>\
`)(fieldScope);
      fieldRow.data('field-id', fid).addClass(`field-${fid}`);

      return fieldRow;
    }

    /*
      * Renders options on the left (worksheet) with those saved in the model
      * Tries to be smart in what it is re-rendering so only changes are rendered.
    */
    render() {
      const forms = [
        { typeName: 'user',  worksheetName: 'user_worksheet' },
        { typeName: 'agent', worksheetName: 'agent_worksheet' }
      ];

      return Array.from(forms).map((form) =>
        this.renderForm(form));
    }

    renderForm(form) {
      let fieldEl, nameCheck, order;
      let prevField   = null;
      let prevFieldEl = null;
      const { typeName }    = form;
      const form_model  = this.ngModel.$viewValue[form.typeName];
      const worksheetEl = this.els[form.worksheetName];
      const tabEl       = this.els[form.typeName + '_tab'];
      const listEl      = worksheetEl.find('ul').first();

      let layoutFieldEls = worksheetEl.find('.layout-field');

      const validNames = [];
      tabEl.find('.form-elements').find('.layout-field').each(function() {
        return validNames.push($(this).data('field-type') + '_' + ($(this).data('field-id') || '0'));
      });

      const use_form_model = [];
      for (var field of Array.from(form_model)) {
        nameCheck = field.field_type + '_' + (field.field_id || '0');
        if (validNames.indexOf(nameCheck) !== -1) {
          use_form_model.push(field);
        }
      }

      const draggableEls = tabEl.find('.form-elements');
      draggableEls.show();
      draggableEls.find('li').each( function() {
        let fid;
        const $el = $(this);
        const field_id = $el.data('field-id') || null;
        if (field_id) {
          fid = $el.data('field-type') + '_' + field_id;
        } else {
          fid = $el.data('field-type');
        }

        return $el.data('fid', fid).attr('data-fid', fid);
      });

      const orderMap = {};
      const elementMap = {};

      // Check for new elements
      const newFields = [];
      for (order = 0; order < use_form_model.length; order++) {
        field = use_form_model[order];
        field.id = this.getFieldId(field);
        fieldEl = layoutFieldEls.filter(`.field-${field.id}`);
        if (!fieldEl[0]) {
          newFields.push(field);
        } else {
          elementMap[field.id] = fieldEl;
        }

        orderMap[field.id] = order;
      }

      // Remove elements
      layoutFieldEls.each( function() {
        const fieldId = $(this).data('field-id');
        if (!elementMap[fieldId]) {
          return $(this).remove();
        }
      });

      // Add new elements
      for (field of Array.from(newFields)) {
        field.id = this.getFieldId(field);

        nameCheck = field.field_type + '_' + (field.field_id || '0');
        if (validNames.indexOf(nameCheck) === -1) {
          continue;
        }

        const fieldRow = this.createFieldRow(typeName, field);
        elementMap[field.id] = fieldRow;
        order = orderMap[field.id];

        if (order === 0) {
          listEl.prepend(fieldRow);
        } else {
          prevField = use_form_model[order-1];
          if (prevField && elementMap[prevField.id]) {
            prevFieldEl = elementMap[prevField.id];
            fieldRow.insertAfter(prevFieldEl);
          } else {
            listEl.append(fieldRow);
          }
        }
      }

      // Verify order
      let doReorder = false;
      layoutFieldEls = worksheetEl.find('.layout-field');
      layoutFieldEls.each( function(currentOrder) {
        const fieldId = $(this).data('field-id');
        const expectedOrder = orderMap[fieldId] || 0;

        if (currentOrder !== expectedOrder) {
          doReorder = true;
          return false;
        }
      });

      if (doReorder) {
        layoutFieldEls.detach();
        for (field of Array.from(use_form_model)) {
          fieldEl = layoutFieldEls.filter(`.field-${field.id}`);
          fieldEl.appendTo(listEl);
        }
      }

      return (() => {
        const result = [];
        for (let f of Array.from(use_form_model)) {
          const fid = this.getFieldId(f);
          result.push(draggableEls.find(`[data-fid=\"${fid}\"]`).hide());
        }
        return result;
      })();
    }

    getFieldId(field) {
      let fid;
      if (field.id) { return field.id; }

      const field_id = field.field_id || null;
      if (field_id) {
        fid = field.field_type + '_' + field_id;
      } else {
        fid = field.field_type;
      }

      field.id = fid;
      return fid;
    }
  }

  return ['$compile', 'LoggerManager', 'DataService', '$q', '$timeout', function($compile, LoggerManager, DataService, $q, $timeout) {

    const directive = {};
    directive.restrict    = 'E';
    directive.require     = 'ngModel';
    directive.templateUrl = "TicketDeps/layout-editor.html";
    directive.replace     = true;
    directive.scope       = {};

    directive.link = function(scope, element, attrs, ngModel) {
      let interfaceHandler;
      const logger = LoggerManager.get('directive.dpLayoutEditor');

      const TicketFields = DataService.get('TicketFields');
      const UserFields   = DataService.get('UserFields');
      const OrgFields   = DataService.get('OrgFields');
      const TicketFieldsPerPerson = DataService.get('CustomFields', 'ticket', 'person');
      const TicketFieldsPerOrg = DataService.get('CustomFields', 'ticket', 'organization');

      return interfaceHandler = new InterfaceHandler(
        scope,
        element,
        attrs,
        ngModel,
        $compile,
        TicketFields,
        UserFields,
        $q,
        $timeout,
        logger,
        TicketFieldsPerPerson,
        TicketFieldsPerOrg,
        OrgFields
      );
    };

    return directive;
  }
  ];
});