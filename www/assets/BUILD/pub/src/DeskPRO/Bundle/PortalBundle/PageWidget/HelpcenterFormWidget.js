import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';
import { HcDateWidget } from './Common/Form/Helpcenter/HcDateWidget';
import DpxCustomFieldAttach from './Common/Form/DpxCustomFieldAttach';
import { DpxDoubleSubmitPrevention } from './Common/Form/DpxDoubleSubmitPrevention';
import DpxFormDraft from './Common/Form/Draft/DpxFormDraft';
import DpxJavascript from './Common/Form/DpxJavascript';
import HcFileUpload from './Common/Form/Helpcenter/HcFileUpload';
import HcProfilePicture from './Common/Form/Helpcenter/HcProfilePicture';
import DpxRte from './Common/Form/DpxRte';
import { HcDpxSelectBox } from './Common/Form/Helpcenter/HcSelectBox';

export default class HelpcenterFormWidget extends PageWidget {

  init() {
    this.addWidgetDef(DpxFormDraft, 'form[data-save-draft]');
    this.addWidgetDef(DpxCustomFieldAttach, '.dpx-custom-field-attach');
    this.addWidgetDef(DpxDoubleSubmitPrevention, '[type="submit"]');
    this.addWidgetDef(DpxJavascript, '.dpx-javascript');
    this.addWidgetDef(HcFileUpload, '.helpcenter-file-upload');
    this.addWidgetDef(HcProfilePicture, '.helpcenter-profile_picture');
    this.addWidgetDef(HcDateWidget, '.dpx-date');
    this.addWidgetDef(HcDateWidget, '.dpx-date-time');
    this.addWidgetDef(HcDpxSelectBox, 'select[dpx-select], select.dpx-select');
    this.addWidgetDef(DpxRte, '[data-rte]');

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
