import _ from "lodash";
import React from "react";
import ReactDOM from "react-dom"
import PageWidget from "DeskPRO/Component/PageWidget/PageWidget";
import DpxSelectBox from "DeskPRO/Bundle/PortalBundle/PageWidget/Common/Form/DpxSelectBox";
import DpxCheckboxGroup from "DeskPRO/Bundle/PortalBundle/PageWidget/Common/Form/DpxCheckboxGroup";
import DpxDateWidget from "DeskPRO/Bundle/PortalBundle/PageWidget/Common/Form/DpxDateWidget";
import DpDropzone from "DeskPRO/Bundle/PortalBundle/PageWidget/Common/Form/DpDropzone";
import NewTicketSuggestions from "DeskPRO/Bundle/PortalBundle/React/NewTicketSuggestions";
import DynamicForm from "DeskPRO/Bundle/AppBundle/Form/DynamicForm.js";



//######################################################################################################################
//# DpInteractiveFormWidget
//######################################################################################################################

export default class DpInteractiveFormWidget extends PageWidget {
  init() {
    this.addWidgetDef(DpxDateWidget, ".dpx-date");
    this.addWidgetDef(DpxDateWidget, ".dpx-date-time");
    this.addWidgetDef(DpxSelectBox, "select[dpx-select]");
    this.addWidgetDef(DpxCheckboxGroup, ".dpx-checkbox-group");
    this.addWidgetDef(DpDropzone, ".dpx-attachements");
  }
}
