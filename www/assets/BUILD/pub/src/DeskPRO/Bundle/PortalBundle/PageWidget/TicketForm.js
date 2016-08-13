import React from 'react';
import ReactDOM from 'react-dom';
import { portalApp } from '../PortalApp';
import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';
import { pageWidgetEmitter } from 'DeskPRO/Component/PageWidget/PageWidgetEmitter';
import { NewTicketSuggestions } from '../React/NewTicketSuggestions';
import { DynamicForm } from '../../AppBundle/Form/DynamicForm';
import _ from 'lodash';
import $ from 'jquery';

class TicketValueReader {

  constructor($formEl) {
    this.$formEl = $formEl;
  }

  parseIntSelect(f) {
    return parseInt(f.val() || 0, 10) || 0;
  }

  getDepartmentId() {
    return this.parseIntSelect($('#ticket_department', this.$formEl));
  }

  getCategoryId() {
    return this.parseIntSelect($('#ticket_category', this.$formEl));
  }

  getPriorityId() {
    return this.parseIntSelect($('#ticket_priority', this.$formEl));
  }

  getProductId() {
    return this.parseIntSelect($('#ticket_product', this.$formEl));
  }

  getOrganizationId() {
    return this.parseIntSelect($('#ticket_user_organization', this.$formEl));
  }

  getWorkflowId() {
    return this.parseIntSelect($('#ticket_workflow', this.$formEl));
  }

  getFieldValue(prefix, fieldId) {
    const id = `#ticket_${prefix}_field_${fieldId}_data`;
    const $field = $(id, this.$formEl);
    if ($field.is(':checkbox')) {
      return $field.is(':checked');
    }
    if ($field.find(`${id}_year`).length) {
      return [
        $(`${id}_year`, $field).val(),
        $(`${id}_month`, $field).val(),
        $(`${id}_day`, $field).val()
      ];
    }
    return $field.val();
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

export class TicketForm extends PageWidget {

  renderWidget() {
    const $formEl = this.$element.find('.dp_ticket_form');
    const $tplEl = this.$element.find('.js_form_tpl');
    const ticketReader = new TicketValueReader($formEl);
    const allFormFields = $([]).add($formEl.find('select, input, textarea')).add($tplEl.find('select, input, textarea'));

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
        }
      },
      fieldFilter: fields => {
        if (!window.DESKPRO_TICKET_DISPLAY) {
          console.error('DESKPRO_TICKET_DISPLAY is not defined');
          return fields;
        }

        const layout = window.DESKPRO_TICKET_DISPLAY.getLayout(ticketReader.getDepartmentId());
        const newFields = _.map(layout.getMatchingFields(ticketReader), (v) => {
          const id = v.id;
          switch (id) {
            case 'subject': return 'subject';
            case 'attachments': return ['attachments', 'more_attachments'];
            default: return id;
          }
        });

        return _.flatten(newFields);
      },
      onFieldsUpdated: event => {
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

    const updateHitter = _.throttle(() => this.dynamicForm.update(), 250);
    allFormFields.on('change', () => setTimeout(() => updateHitter(), 0));
  }
}
