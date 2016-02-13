import $ from 'jquery';
import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';
import * as PortalSelectBox from '../../../React/Form/PortalSelectBox';

export class DpxSelectBox extends PageWidget {

  renderWidget() {
    this.$element.hide();
    this.$rElement = $('<div class="dp-react-widget as-dpui"></div>').insertAfter(this.$element);
    this.actionStore = PortalSelectBox.createComponent(this.$element, this.$rElement, this.options);
  }
}
