import _ from "lodash";
import React from "react";
import ReactDOM from "react-dom"
import PageWidget from "DeskPRO/Component/PageWidget/PageWidget";
import DpLevelSelect from "DeskPRO/Bundle/PortalBundle/PageWidget/Common/Form/DpLevelSelect";
import DpCheckboxGroup from "DeskPRO/Bundle/PortalBundle/PageWidget/Common/Form/DpCheckboxGroup";
import DpDropzone from "DeskPRO/Bundle/PortalBundle/PageWidget/Common/Form/DpDropzone";
import NewTicketSuggestions from "DeskPRO/Bundle/PortalBundle/React/NewTicketSuggestions";
import DynamicForm from "DeskPRO/Bundle/AppBundle/Form/DynamicForm.js";



//######################################################################################################################
//# DpInteractiveFormWidget
//######################################################################################################################

export default class DpInteractiveFormWidget extends PageWidget {
  init() {
    this.addWidgetDef(DpLevelSelect, "select[dpx-select]");
    this.addWidgetDef(DpCheckboxGroup, ".dpx-checkbox-group");
    this.addWidgetDef(DpDropzone, ".dpx-attachements");
  }
  renderWidget() {

  }
}
