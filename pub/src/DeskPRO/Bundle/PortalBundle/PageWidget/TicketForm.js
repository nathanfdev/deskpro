import React from 'react';
import ReactDOM from 'react-dom';
import PortalApp from 'DeskPRO/Bundle/PortalBundle/PortalApp';
import PageWidget from 'DeskPRO/Component/PageWidget/PageWidget';
import NewTicketSuggestions from 'DeskPRO/Bundle/PortalBundle/React/NewTicketSuggestions';
import DynamicForm from 'DeskPRO/Bundle/AppBundle/Form/DynamicForm.js';
import _ from 'lodash';
import $ from 'jquery';

// Ticket value reader
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

// Page widget
export default class TicketForm extends PageWidget {

  renderWidget() {
    const $formEl = this.$element.find('.dp_ticket_form');
    const formName = $formEl.find('form').attr('name');
    const $tplEl = this.$element.find('.js_form_tpl');
    const ticketReader = new TicketValueReader($formEl);
    const allFormFields = $([]).add($formEl.find('select')).add($tplEl.find('select'));

    let updateHitter;
    let updateLastDepId;
    let setDisplayedFields;

    updateLastDepId = () => {
      const depId = ticketReader.getDepartmentId();
      const $ldp = $formEl.find("[data-field='last_department_id']");

      if ($ldp.length) {
        if ($ldp.find('input').length) {
          $ldp.find('input').val(depId);
        } else {
          $ldp.val(depId); // on first page load this is the case
        }
      } else {
        $formEl.find('form').append('<input data-field="last_department_id" type="hidden" name="' + formName + '[last_department_id]" value="' + depId + '" />');
      }
    };

    setDisplayedFields = (e) => {
      const displayedFields = e.inst.currentFields.filter((field) => {
        return field !== 'displayed_fields'; // don't include this special field in the list
      }).join(',');
      const $df = $formEl.find("[data-field='displayed_fields']").find('input[type="hidden"]');
      console.log('[TicketForm] [setDisplayedFields] setting displayed_fields to: ', displayedFields);
      $df.val(displayedFields);
    };

    this.dynForm = new DynamicForm({
      formEl: $formEl,
      tplEl: $tplEl,
      alwaysFields: ['department', 'user_name_and_email', 'user_email', 'subject', 'message', 'submit', 'last_department_id', 'displayed_fields'],
      onInit: () => {
        updateLastDepId();

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
            case 'attach': return ['attachments', 'more_attachments'];
            default: return id;
          }
        });

        return _.flatten(newFields);
      },
      onFieldsUpdated: (fields) => {
        updateLastDepId();
        setDisplayedFields(fields);
      },
      onPostUpdate: () => {
        const portalPage = PortalApp.getPortalPage();
        if (portalPage) {
          portalPage.refresh($formEl);
        }
      }
    });

    updateHitter = _.throttle(()=> this.dynForm.update(), 250);
    allFormFields.on('change', updateHitter);
  }
}
