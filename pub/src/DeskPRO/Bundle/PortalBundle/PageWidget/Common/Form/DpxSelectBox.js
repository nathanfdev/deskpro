import $ from "jquery";
import PageWidget from "DeskPRO/Component/PageWidget/PageWidget";
import * as PortalSelectBox from "DeskPRO/Bundle/PortalBundle/React/Form/PortalSelectBox";

export default class DpxLevelSelect extends PageWidget {
  renderWidget() {
    this.$element.hide();
    this.$rElement = $('<div class="dp-react-widget as-dpui"></div>').insertAfter(this.$element);
    this.actionStore = PortalSelectBox.createComponent(this.$element, this.$rElement);
  }
}
