import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';
import $ from 'jquery';
import Handlebars from 'handlebars';

export default class DpxJavascript extends PageWidget {

  renderWidget() {
    const $el = this.$element;
    const $input = $el.find('input.js-custom-field-hidden-input');
    let evCode;
    eval(`evCode = ${$el.data(('code'))}`); // eslint-disable-line no-eval
    const ctx = {
      Handlebars,
      jQuery:    $,
      interface: 'portal',
      context:   $input.parents('form').data('visibility') === 'new' ? 'newticket' : 'viewticket'
    };
    evCode(ctx);
    const fieldData = JSON.parse($input.val() ? $input.val() : '{}') || { value: null, data: {} };
    const jsWidget = {
      ctx,
      element:      null,
      field:        $input,
      currentData:  fieldData.data,
      currentValue: fieldData.value
    };

    const $renderedElement = ctx.renderField(
      (value, data) => {
        let dataObject = { value: null, data: null };
        if (
          (value === null || typeof value === 'undefined')
          && (data === null || typeof data === 'undefined')
        ) {
          dataObject.value = null;
          dataObject.data  = null;
        } else {
          dataObject = Object.assign({}, { value }, { data: data || {} });
        }
        jsWidget.field.val(JSON.stringify(dataObject));
        jsWidget.currentData = dataObject.data;
        jsWidget.currentValue = dataObject.value;
      },
      jsWidget.currentValue, jsWidget.currentData
    );
    jsWidget.field.after($renderedElement);
    jsWidget.element = $renderedElement;
  }
}
