import $ from 'jquery';
import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';
import * as PortalMultipleSelectBox from '../../../React/Form/PortalMultipleSelectBox';

export class DpxMultipleSelectBox extends PageWidget {

  renderWidget() {
    this.$element.hide();
    this.$rElement = $('<div class="dp-react-widget as-dpui"></div>').insertAfter(this.$element);
    this.actionStore = PortalMultipleSelectBox.createComponent(this.$element, this.$rElement, null, this.options);
  }
}
