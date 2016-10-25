import React from 'react';
import ReactDOM from 'react-dom';
import _ from 'lodash';
import $ from 'jquery';
import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';
import { pageWidgetEmitter } from 'DeskPRO/Component/PageWidget/PageWidgetEmitter';
import { portalApp } from '../PortalApp';
import { NewTicketSuggestions } from '../React/NewTicketSuggestions';
import { DynamicForm } from '../../AppBundle/Form/DynamicForm';

const parseIntSelect = f => parseInt(f.val() || 0, 10) || 0;

class TicketValueReader {

  constructor($formEl) {
    this.$formEl = $formEl;
  }

  getDepartmentId() {
    return parseIntSelect($('#ticket_department', this.$formEl));
  }

  getCategoryId() {
    return parseIntSelect($('#ticket_category', this.$formEl));
  }

  getPriorityId() {
    return parseIntSelect($('#ticket_priority', this.$formEl));
  }

  getProductId() {
    return parseIntSelect($('#ticket_product', this.$formEl));
  }

  getOrganizationId() {
    return parseIntSelect($('#ticket_user_organization', this.$formEl));
  }

  getWorkflowId() {
    return parseIntSelect($('#ticket_workflow', this.$formEl));
  }
}

export default class TicketForm extends PageWidget {

  renderWidget() {
    const $formEl = this.$element.find('.dp_ticket_form');
    const $tplEl = this.$element.find('.js_form_tpl');
    const ticketReader = new TicketValueReader($formEl);
    const allFormFields = $([]).add($formEl.find('select')).add($tplEl.find('select'));

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
      fieldFilter: (fields) => {
        if (!window.DESKPRO_TICKET_DISPLAY) {
          console.error('DESKPRO_TICKET_DISPLAY is not defined');
          return fields;
        }

        const layout = window.DESKPRO_TICKET_DISPLAY.getLayout(ticketReader.getDepartmentId());

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

        newFields = _.map(newFields, (v) => {
          const id = v.id;
          switch (id) {
            case 'subject': return 'subject';
            case 'attachments': return ['attachments', 'more_attachments'];
            default: return id;
          }
        });

        return _.flatten(newFields);
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

    const updateHitter = _.throttle(() => this.dynamicForm.update(), 250);
    allFormFields.on('change', () => setTimeout(() => updateHitter(), 0));
  }
}
