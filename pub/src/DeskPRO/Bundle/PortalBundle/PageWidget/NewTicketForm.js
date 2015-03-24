import PageWidget from "DeskPRO/Component/PageWidget/PageWidget";
import DpLevelSelect from "DeskPRO/Component/PageWidget/Form/DpLevelSelect";

export default class NewTicketForm extends PageWidget {
  init() {
    this.addWidgetDef(DpLevelSelect, "select[dp-select]");
  }

  renderWidget() {
    console.log("NEW TICKET RENDERED");
  }
}