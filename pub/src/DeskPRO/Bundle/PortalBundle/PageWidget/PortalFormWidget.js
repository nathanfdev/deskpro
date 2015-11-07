import _ from "lodash";
import PageWidget from "DeskPRO/Component/PageWidget/PageWidget";
import DpxSelectBox from "DeskPRO/Bundle/PortalBundle/PageWidget/Common/Form/DpxSelectBox";
import DpxMultipleSelectBox from "DeskPRO/Bundle/PortalBundle/PageWidget/Common/Form/DpxMultipleSelectBox";
import DpxCheckboxGroup from "DeskPRO/Bundle/PortalBundle/PageWidget/Common/Form/DpxCheckboxGroup";
import DpxDateWidget from "DeskPRO/Bundle/PortalBundle/PageWidget/Common/Form/DpxDateWidget";
import DpDropzone from "DeskPRO/Bundle/PortalBundle/PageWidget/Common/Form/DpDropzone";
import DpxRte from "DeskPRO/Bundle/PortalBundle/PageWidget/Common/Form/DpxRte";



//######################################################################################################################
//# DpInteractiveFormWidget
//######################################################################################################################

export default class DpInteractiveFormWidget extends PageWidget {
  init() {
    this.addWidgetDef(DpxDateWidget, ".dpx-date");
    this.addWidgetDef(DpxDateWidget, ".dpx-date-time");
    this.addWidgetDef(DpxSelectBox, "select[dpx-select]");
    this.addWidgetDef(DpxMultipleSelectBox, "select[dpx-select-multiple]");
    this.addWidgetDef(DpxCheckboxGroup, ".dpx-checkbox-group");
    this.addWidgetDef(DpDropzone, ".dpx-attachements");
    this.addWidgetDef(DpxRte, "[data-rte]");
  }
}
