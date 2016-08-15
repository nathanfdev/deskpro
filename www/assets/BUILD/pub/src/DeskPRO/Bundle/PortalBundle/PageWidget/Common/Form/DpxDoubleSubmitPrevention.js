import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';
import 'jquery-serializejson/jquery.serializejson';

export class DpxDoubleSubmitPrevention extends PageWidget {

  renderWidget() {
    const $el = this.$element;
    const $form = this.$element.closest('form');
    if (!$form) {
      return;
    }

    let prevFormData;
    let formData;

    $el.on('click', () => {
      formData = $form.serializeJSON();
      if (JSON.stringify(formData) === JSON.stringify(prevFormData)) {
        return false;
      }

      prevFormData = formData;

      return true;
    });
  }
}
