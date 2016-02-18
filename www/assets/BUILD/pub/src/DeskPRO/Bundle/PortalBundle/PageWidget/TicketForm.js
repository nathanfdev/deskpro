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

  _parseIntSelect(f) {
    return parseInt(f.val() || 0, 10) || 0;
  }

  getDepartmentId() {
    return this._parseIntSelect($('#ticket_department', this.$formEl));
  }

  getCategoryId() {
    return this._parseIntSelect($('#ticket_category', this.$formEl));
  }

  getPriorityId() {
    return this._parseIntSelect($('#ticket_priority', this.$formEl));
  }

  getProductId() {
    return this._parseIntSelect($('#ticket_product', this.$formEl));
  }

  getOrganizationId() {
    return this._parseIntSelect($('#ticket_user_organization', this.$formEl));
  }

  getWorkflowId() {
    return this._parseIntSelect($('#ticket_workflow', this.$formEl));
  }
}

export class TicketForm extends PageWidget {

  renderWidget() {
    const $formEl = this.$element.find('.dp_ticket_form');
    const formName = $formEl.find('form').attr('name');
    const $tplEl = this.$element.find('.js_form_tpl');
    const ticketReader = new TicketValueReader($formEl);
    const allFormFields = $([]).add($formEl.find('select')).add($tplEl.find('select'));

    let updateHitter;
    let setDisplayedFields;

    $('#ticket_message_message_html', this.$formEl).attr('data-blob-path', 'ticket[attachments]');

    setDisplayedFields = event => {
      // handle special field "attachments"
      // we remove the unnecessary and "more_attachments" from the string
      const theFields = event.inst.currentFields;

      const displayedFields = theFields.filter(field => !_.includes(['displayed_fields', 'more_attachments'], field)).join(',');
      const $df = $formEl.find("[data-field='displayed_fields']").find('input[type="hidden"]');

      console.log('[TicketForm] [setDisplayedFields] setting displayed_fields to: ', displayedFields);
      $df.val(displayedFields);
    };

    this.dynamicForm = new DynamicForm({
      formEl: $formEl,
      tplEl: $tplEl,
      alwaysFields: ['department', 'person', 'user_email', 'subject', 'message', 'submit', 'displayed_fields'],
      onInit: () => {
        // only render ticket deflection if a .dpx-with-ticket-deflection is present on the form
        if ($formEl.hasClass('dpx-with-ticket-deflection')) {
          const $subject = $('#ticket_subject', $formEl);
          const $rElement = $('<div class="dp-react-widget"></div>').insertAfter($subject);

          ReactDOM.render(React.createElement(NewTicketSuggestions, {input: $subject}), $rElement.get(0));
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
      onFieldsUpdated: fields => {
        setDisplayedFields(fields);
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

    updateHitter = _.throttle(()=> this.dynamicForm.update(), 250);
    allFormFields.on('change', () => setTimeout(() => updateHitter(), 0));
  }
}
