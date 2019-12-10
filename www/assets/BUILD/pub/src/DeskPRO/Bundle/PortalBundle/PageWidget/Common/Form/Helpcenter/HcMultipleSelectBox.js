import $ from 'jquery';
import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';
import * as HcMultipleSelectBox from '../../../../React/Form/HcMultipleSelectBox';

export class HcDpxMultipleSelectBox extends PageWidget {
  renderWidget() {
    this.$element.hide();
    this.$rElement = $('<div class="dp-react-widget dp-pc_field as-dpui"></div>').insertAfter(this.$element);
    this.actionStore = HcMultipleSelectBox.createComponent(this.$element, this.$rElement, this.options);
  }
}
