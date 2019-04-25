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
      context:   'newticket',
    };
    evCode(ctx);
    evCode = {
      ctx,
      element: null,
      field:   $input,
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
        evCode.field.val(JSON.stringify(dataObject));
      },
      '', {}
    );
    evCode.field.after($renderedElement);
    evCode.element = $renderedElement;
  }
}
