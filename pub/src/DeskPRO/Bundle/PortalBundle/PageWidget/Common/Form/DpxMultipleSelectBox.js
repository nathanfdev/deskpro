import $ from "jquery";
import PageWidget from "DeskPRO/Component/PageWidget/PageWidget";
import * as PortalMultipleSelectBox from "DeskPRO/Bundle/PortalBundle/React/Form/PortalMultipleSelectBox";

export default class DpxLevelSelect extends PageWidget {
  renderWidget() {
    this.$element.hide();
    this.$rElement = $('<div class="dp-react-widget"></div>').insertAfter(this.$element);
    this.actionStore = PortalMultipleSelectBox.createComponent(this.$element, this.$rElement);
  }
}
