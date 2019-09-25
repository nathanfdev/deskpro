import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';
import { HelpcenterSelectBox } from './Common/Form/Helpcenter/HelpcenterSelectBox';

export default class HelpcenterFormWidget extends PageWidget {

  init() {
    this.addWidgetDef(HelpcenterSelectBox, 'select[dpx-select], select.dpx-select');

    if (this.$element.is('form')) {
      HelpcenterFormWidget.initForms(this.$element);
    } else {
      HelpcenterFormWidget.initForms(this.$element.find('form'));
    }
  }

  static initForms($forms) {
    // Disable pressing enter from submitting forms by accident
    $forms.find('input, select').not('[type="submit"], [type="reset"], [type="button"]').on('keyup keypress', (ev) => {
      const keyCode = ev.keyCode || ev.which;
      if (keyCode === 13) {
        ev.preventDefault();
        return false;
      }

      return true;
    });
  }
}
