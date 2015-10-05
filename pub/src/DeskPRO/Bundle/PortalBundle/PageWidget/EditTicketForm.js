import PageWidget from "DeskPRO/Component/PageWidget/PageWidget";
import DpLevelSelect from "DeskPRO/Bundle/PortalBundle/PageWidget/Common/Form/DpLevelSelect";

export default class NewTicketForm extends PageWidget {
  init() {
    this.addWidgetDef(DpLevelSelect, "select[dp-select]");
  }
}
