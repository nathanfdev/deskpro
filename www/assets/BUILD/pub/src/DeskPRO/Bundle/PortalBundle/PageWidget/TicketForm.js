import React from 'react';
import ReactDOM from 'react-dom';
import map from 'lodash/map';
import flatten from 'lodash/flatten';
import throttle from 'lodash/throttle';
import $ from 'jquery';
import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';
import { pageWidgetEmitter } from 'DeskPRO/Component/PageWidget/PageWidgetEmitter';
import { NewTicketSuggestions } from '../React/NewTicketSuggestions';
import { DynamicForm } from '../../AppBundle/Form/DynamicForm';

class TicketValueReader {

  constructor($formEl, $fullFormEl) {
    this.$formEl = $formEl;
    // $fullFormEl used to get field value not presented in main $formEl but used in field criteria(condition)
    this.$fullFormEl = $fullFormEl;
  }

  static parseIntSelect(f) {
    return parseInt(f.val() || 0, 10) || 0;
  }

  setHiddenFields(hiddenFields) {
    this.hiddenFields = hiddenFields;
  }

  isFieldHidden(fieldName) {
    return this.hiddenFields && this.hiddenFields.indexOf(fieldName) !== -1;
  }

  getDepartmentId() {
    const $field = $('#ticket_department', this.$formEl);

    if ($field.length) {
      return TicketValueReader.parseIntSelect($field);
    }

    return TicketValueReader.parseIntSelect($('#ticket_department', this.$fullFormEl));
  }

  getCategoryId() {
    const $field = $('#ticket_category', this.$formEl);

    if ($field.length) {
      if (this.isFieldHidden('category')) {
        return null;
      }

      return TicketValueReader.parseIntSelect($field);
    }

    return TicketValueReader.parseIntSelect($('#ticket_category', this.$fullFormEl));
  }

  getPriorityId() {
    const $field = $('#ticket_priority', this.$formEl);

    if ($field.length) {
      if (this.isFieldHidden('priority')) {
        return null;
      }

      return TicketValueReader.parseIntSelect($field);
    }

    return TicketValueReader.parseIntSelect($('#ticket_priority', this.$fullFormEl));
  }

  getProductId() {
    const $field = $('#ticket_product', this.$formEl);

    if ($field.length) {
      if (this.isFieldHidden('product')) {
        return null;
      }

      return TicketValueReader.parseIntSelect($field);
    }

    return TicketValueReader.parseIntSelect($('#ticket_product', this.$fullFormEl));
  }

  getOrganizationId() {
    const $field = $('#ticket_user_organization', this.$formEl);

    if ($field.length) {
      if (this.isFieldHidden('user_organization')) {
        return null;
      }

      return TicketValueReader.parseIntSelect($field);
    }

    return TicketValueReader.parseIntSelect($('#ticket_user_organization', this.$fullFormEl));
  }

  getWorkflowId() {
    const $field = $('#ticket_workflow', this.$formEl);

    if ($field.length) {
      if (this.isFieldHidden('workflow')) {
        return null;
      }

      return TicketValueReader.parseIntSelect($field);
    }

    return TicketValueReader.parseIntSelect($('#ticket_workflow', this.$fullFormEl));
  }

  getFieldValue(prefix, fieldId, useFullForm = false) {
    const $formEl = useFullForm ? this.$fullFormEl : this.$formEl;
    const fieldName = `${prefix}_field_${fieldId}`;

    if (!useFullForm && this.isFieldHidden(fieldName)) {
      return null;
    }

    const id = `#ticket_${fieldName}_data`;
    let $field = $(id, $formEl);

    // toggle
    if ($field.is(':checkbox')) {
      return $field.is(':checked');
    }

    if ($field.is('select, input, textarea')) {
      return $field.val();
    }

    // date and datetime widgets
    if ($field.find(`${id}_year`).length) {
      const year = $(`${id}_year`, $field).val();
      const month = $(`${id}_month`, $field).val();
      const day = $(`${id}_day`, $field).val();
      return `${year}-${month}-${day}`;
    }

    // date and datetime widgets (`dpx-date-time` version)
    if ($field.find(`${id}_date`).length) {
      const year = $(`${id}_date_year`, $field).val();
      const month = $(`${id}_date_month`, $field).val();
      const day = $(`${id}_date_day`, $field).val();
      return `${year}-${month}-${day}`;
    }

    // choice of checkboxes, radio
    const name = `ticket[${prefix}_field_${fieldId}]`;
    $field = $(`[name="${name}[data]"], [name="${name}[data][]"]`, $formEl);
    if ($field.length) {
      return $field.filter(':checked').map((i, el) => el.value).get();
    }

    // display field
    $field = $(`#ticket_${prefix}_field_${fieldId}`, $formEl);
    if ($field.length) {
      return $.trim($field.text());
    }

    return null;
  }

  getTicketFieldValue(fieldId) {
    let res = this.getFieldValue('ticket', fieldId, false);
    if (res === null) {
      res = this.getFieldValue('ticket', fieldId, true);
    }
    return res;
  }

  getUserFieldValue(fieldId) {
    let res = this.getFieldValue('user', fieldId, false);
    if (res === null) {
      res = this.getFieldValue('user', fieldId, true);
    }
    return res;
  }

  getOrgFieldValue(fieldId) {
    let res = this.getFieldValue('org', fieldId, false);
    if (res === null) {
      res = this.getFieldValue('org', fieldId, true);
    }
    return res;
  }
}

export default class TicketForm extends PageWidget {

  renderWidget() {
    const $formEl = this.$element.find('.dp_ticket_form');
    const $tplEl = this.$element.find('.js_form_tpl');
    const ticketReader = new TicketValueReader($formEl, $tplEl);
    const allFormFields = $([])
      .add($formEl.find('select, input, textarea'))
      .add($tplEl.find('select, input, textarea'));

    $('#ticket_message_message_html', this.$formEl).attr('data-blob-path', 'ticket[attachments]');

    this.dynamicForm = new DynamicForm({
      formEl:       $formEl,
      tplEl:        $tplEl,
      alwaysFields: ['department', 'subject', 'message', 'submit', 'displayed_fields'],
      onInit:       () => {
        // only render ticket deflection if a .dpx-with-ticket-deflection is present on the form
        if ($formEl.hasClass('dpx-with-ticket-deflection')) {
          const $subject = $('#ticket_subject', $formEl);
          const $rElement = $('<div class="dp-react-widget"></div>').insertAfter($subject);

          ReactDOM.render(React.createElement(NewTicketSuggestions, { input: $subject }), $rElement.get(0));

          $formEl.find('input:visible, textarea:visible').first().focus();
        }
      },
      fieldFilter: (fields, dynamicForm) => {
        if (!window.DESKPRO_TICKET_DISPLAY) {
          console.error('DESKPRO_TICKET_DISPLAY is not defined');
          return fields;
        }

        const layout = window.DESKPRO_TICKET_DISPLAY.getLayout(ticketReader.getDepartmentId());
        ticketReader.setHiddenFields(dynamicForm.getHiddenFields());

        let newFields = layout.getMatchingFields(ticketReader);
        let filterFn;

        switch ($formEl.find('form').data('visibility')) {
          case 'view':
            filterFn = i => (!!i.isVisibleOnView) || (!!i.isVisibleOnViewAlways);
            break;
          case 'edit':
            filterFn = i => !!i.isVisibleOnEdit;
            break;
          case 'new':
          default:
            filterFn = i => !!i.isVisibleOnNew;
            break;
        }

        if (filterFn) {
          newFields = newFields.filter(filterFn);
        }

        const hasOrganization = !!$formEl.find('#ticket_person_user_name').data('organization-id');
        if (!hasOrganization) {
          newFields = newFields.filter(field => !/^org_field_/.test(field.id));
        }

        newFields = map(newFields, (v) => {
          const id = v.id;
          switch (id) {
            case 'attachments': return ['attachments', 'more_attachments'];
            default: return id;
          }
        });

        return flatten(newFields);
      },
      onFieldsUpdated: (event) => {
        const $df = $formEl.find("[data-field='displayed_fields']").find('input[type="hidden"]');
        $df.val(event.inst.currentFields);
      },
      onPostUpdate: () => {
        // using magic global here so as not to require importing portalApp
        // this lib is alsoused in Widget bundle -- so requiring portalApp would
        // inflate the bundle size needlessly
        const portalPage = window.DESKPRO_PORTAL_PAGE;
        if (portalPage) {
          portalPage.refresh($formEl);
        } else {
          pageWidgetEmitter.emit('refresh', this);
        }
      }
    });

    if (window.DP_FIELDS_INIT_CALLBACK) {
      this.dynamicForm.ee.on('fieldsUpdated', (evData) => {
        window.DP_FIELDS_INIT_CALLBACK(evData);
      });
    }

    const updateHitter = throttle(() => this.dynamicForm.update(), 250);
    allFormFields.on('change', () => {
      setTimeout(() => updateHitter(), 0);
    });
    allFormFields.change(() => {
      setTimeout(() => updateHitter(), 0);
    });
  }
}
