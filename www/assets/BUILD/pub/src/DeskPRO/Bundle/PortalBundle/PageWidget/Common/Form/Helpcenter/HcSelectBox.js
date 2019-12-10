import $ from 'jquery';
import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';
import * as HcSelectBox from '../../../../React/Form/HcSelectBox';

export class HcDpxSelectBox extends PageWidget {
  renderWidget() {
    this.$element.hide();
    this.$rElement = $('<div class="dp-react-widget as-dpui"></div>').insertAfter(this.$element);
    this.actionStore = HcSelectBox.createComponent(this.$element, this.$rElement, this.options);
  }
}
