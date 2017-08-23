import React from 'react';
import ReactDOM from 'react-dom';
import map from 'lodash/map';
import flatten from 'lodash/flatten';
import throttle from 'lodash/throttle';
import $ from 'jquery';
import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';
import { pageWidgetEmitter } from 'DeskPRO/Component/PageWidget/PageWidgetEmitter';
import { portalApp } from '../PortalApp';
import { NewTicketSuggestions } from '../React/NewTicketSuggestions';
import { DynamicForm } from '../../AppBundle/Form/DynamicForm';

class TicketValueReader {

  constructor($formEl) {
    this.$formEl = $formEl;
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
    return TicketValueReader.parseIntSelect($('#ticket_department', this.$formEl));
  }

  getCategoryId() {
    if (this.isFieldHidden('category')) {
      return null;
    }

    return TicketValueReader.parseIntSelect($('#ticket_category', this.$formEl));
  }

  getPriorityId() {
    if (this.isFieldHidden('priority')) {
      return null;
    }

    return TicketValueReader.parseIntSelect($('#ticket_priority', this.$formEl));
  }

  getProductId() {
    if (this.isFieldHidden('product')) {
      return null;
    }

    return TicketValueReader.parseIntSelect($('#ticket_product', this.$formEl));
  }

  getOrganizationId() {
    if (this.isFieldHidden('user_organization')) {
      return null;
    }

    return TicketValueReader.parseIntSelect($('#ticket_user_organization', this.$formEl));
  }

  getWorkflowId() {
    if (this.isFieldHidden('workflow')) {
      return null;
    }

    return TicketValueReader.parseIntSelect($('#ticket_workflow', this.$formEl));
  }

  getFieldValue(prefix, fieldId) {
    const fieldName = `${prefix}_field_${fieldId}`;

    if (this.isFieldHidden(fieldName)) {
      return null;
    }

    const id = `#ticket_${fieldName}_data`;
    let $field = $(id, this.$formEl);

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

    // choice of checkboxes, radio
    const name = `ticket[${prefix}_field_${fieldId}]`;
    $field = $(`[name="${name}[data]"], [name="${name}[data][]"]`, this.$formEl);
    if ($field.length) {
      return $field.filter(':checked').map((i, el) => el.value).get();
    }

    // display field
    $field = $(`#ticket_${prefix}_field_${fieldId}`, this.$formEl);
    if ($field.length) {
      return $.trim($field.text());
    }

    return null;
  }

  getTicketFieldValue(fieldId) {
    return this.getFieldValue('ticket', fieldId);
  }

  getUserFieldValue(fieldId) {
    return this.getFieldValue('user', fieldId);
  }

  getOrgFieldValue(fieldId) {
    return this.getFieldValue('org', fieldId);
  }
}

export default class TicketForm extends PageWidget {

  renderWidget() {
    const $formEl = this.$element.find('.dp_ticket_form');
    const $tplEl = this.$element.find('.js_form_tpl');
    const ticketReader = new TicketValueReader($formEl);
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

        newFields = map(layout.getMatchingFields(ticketReader), (v) => {
          const id = v.id;
          switch (id) {
            case 'subject': return 'subject';
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
        const portalPage = portalApp.getPortalPage();
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
    allFormFields.on('change', () => setTimeout(() => updateHitter(), 0));
  }
}
