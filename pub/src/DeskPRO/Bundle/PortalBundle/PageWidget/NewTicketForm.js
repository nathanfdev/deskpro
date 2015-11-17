import _ from "lodash";
import React from "react";
import ReactDOM from "react-dom"
import PortalApp from "DeskPRO/Bundle/PortalBundle/PortalApp";
import PageWidget from "DeskPRO/Component/PageWidget/PageWidget";
import NewTicketSuggestions from "DeskPRO/Bundle/PortalBundle/React/NewTicketSuggestions";
import DynamicForm from "DeskPRO/Bundle/AppBundle/Form/DynamicForm.js";

//######################################################################################################################
//# Ticket value reader
//######################################################################################################################

class TicketValueReader {
  constructor($formEl) {
    this.$formEl = $formEl;
  }

  _parseIntSelect(f) {
    return parseInt(f.val() || 0) || 0;
  }

  getDepartmentId() {
    return this._parseIntSelect($('#ticket_department'));
  }

  getCategoryId() {
    return this._parseIntSelect($('#ticket_category'));
  }

  getPriorityId() {
    return this._parseIntSelect($('#ticket_priority'));
  }

  getProductId() {
    return this._parseIntSelect($('#ticket_product'));
  }

  getOrganizationId() {
    return this._parseIntSelect($('#ticket_user_organization'));;
  }

  getWorkflowId() {
    return this._parseIntSelect($('#ticket_workflow'));
  }
}


//######################################################################################################################
//# Page widget
//######################################################################################################################

export default class NewTicketForm extends PageWidget {
  renderWidget() {
    let $formEl       = this.$element.find('.dp_ticket_form');
    let formName     = $formEl.find('form').attr('name');
    let $tplEl        = this.$element.find('.js_form_tpl');
    let ticketReader  = new TicketValueReader($formEl);
    let allFormFields = $([]).add($formEl.find('select')).add($tplEl.find('select'));
    let updateHitter;
    let updateLastDepId;
    let setDisplayedFields;

    updateLastDepId = () => {
      let dep_id = ticketReader.getDepartmentId();

      let $ldp = $formEl.find("[data-field='last_department_id']");
      if ($ldp.length) {
        if($ldp.find('input').length) {
          $ldp.find('input').val(dep_id);
        } else {
          $ldp.val(dep_id); // on first page load this is the case
        }
      } else {
        $formEl.find('form').append('<input data-field="last_department_id" type="hidden" name="' + formName + '[last_department_id]" value="' + dep_id + '" />');
      }
    };

    setDisplayedFields = (e) => {
      const displayed_fields = e.inst.currentFields.filter((field) => {
          return field != 'displayed_fields'; // don't include this special field in the list
      }).join(',');
      let $df = $formEl.find("[data-field='displayed_fields']").find('input[type="hidden"]');
      console.log('[NewTicketForm] [setDisplayedFields] setting displayed_fields to: ', displayed_fields);
      $df.val(displayed_fields)
    };

    this.dynForm = new DynamicForm({
      formEl: $formEl,
      tplEl:  $tplEl,
      alwaysFields: ['department', 'user_name_and_email', 'user_email', 'subject', 'message', 'submit', 'last_department_id', 'displayed_fields'],
      onInit: () => {
        updateLastDepId();

        let $subject = $('#ticket_subject');
        let $rElement = $('<div class="dp-react-widget"></div>').insertAfter($subject);
        ReactDOM.render(React.createElement(NewTicketSuggestions, {input: $subject}), $rElement.get(0));
      },
      fieldFilter: (fields, currentFields, dynForm) => {
        if (!window.DESKPRO_TICKET_DISPLAY) {
          console.error("DESKPRO_TICKET_DISPLAY is not defined");
          return fields;
        }

        let layout = window.DESKPRO_TICKET_DISPLAY.getLayout(ticketReader.getDepartmentId());
        let newFields = _.map(layout.getMatchingFields(ticketReader), (v) => {
          let id = v.id;
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
        PortalApp.getPortalPage().refresh($formEl);
      }
    });

    updateHitter = _.throttle(()=> {
      this.dynForm.update();
    }, 250);

    allFormFields.on('change', function() {
      updateHitter();
    });
  }
}
