import $ from "jquery";
import PageWidget from "DeskPRO/Component/PageWidget/PageWidget";
import * as ReactLevelSelect from "DeskPRO/Bundle/AppBundle/React/Standalone/DpLevelSelect2";

export default class DpLevelSelect extends PageWidget {
  renderWidget() {
    this.$element.hide();
    this.$rElement = $('<div class="dp-react-widget"></div>').insertAfter(this.$element);
    this.actionStore = ReactLevelSelect.createComponent(this.$element, this.$rElement);
  }
}
