import _ from "lodash";
import PageWidget from "DeskPRO/Component/PageWidget/PageWidget";
import DpLevelSelect from "DeskPRO/Bundle/PortalBundle/PageWidget/Common/Form/DpLevelSelect";
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
  init() {
    this.addWidgetDef(DpLevelSelect, "select[dp-select]");
  }

  renderWidget() {
    let $formEl       = this.$element.find('.dp_ticket_form');
    let $tplEl        = this.$element.find('.js_form_tpl');
    let ticketReader  = new TicketValueReader($formEl);
    let allFormFields = $([]).add($formEl.find('select')).add($tplEl.find('select'));
    let updateHitter;

    this.dynForm = new DynamicForm({
      formEl: $formEl,
      tplEl:  $tplEl,
      alwaysFields: ['user_email', 'subject', 'message', 'submit'],
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
      onFieldsUpdated: () => {
        this.runWidgets($formEl);
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