import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';
import { DpxSelectBox } from './Common/Form/DpxSelectBox';
import { DpxMultipleSelectBox } from './Common/Form/DpxMultipleSelectBox';
import { DpxCheckboxGroup } from './Common/Form/DpxCheckboxGroup';
import { DpxDateWidget } from './Common/Form/DpxDateWidget';
import DpxAttach from './Common/Form/DpxAttach';
import DpxRte from './Common/Form/DpxRte';
import DpxCustomFieldAttach from './Common/Form/DpxCustomFieldAttach';
import { DpxRadio } from './Common/Form/DpxRadio';
import { DpxDoubleSubmitPrevention } from './Common/Form/DpxDoubleSubmitPrevention';
import DpxFormDraft from './Common/Form/Draft/DpxFormDraft';

export default class PortalFormWidget extends PageWidget {

  init() {
    this.addWidgetDef(DpxDateWidget, '.dpx-date');
    this.addWidgetDef(DpxDateWidget, '.dpx-date-time');
    this.addWidgetDef(DpxSelectBox, 'select[dpx-select], select.dpx-select');
    this.addWidgetDef(DpxMultipleSelectBox, 'select[dpx-select-multiple], select.dpx-select-multiple');
    this.addWidgetDef(DpxCheckboxGroup, '.dpx-checkbox-group');
    this.addWidgetDef(DpxAttach, '.dpx-attach');
    this.addWidgetDef(DpxRte, '[data-rte]');
    this.addWidgetDef(DpxRadio, '.dpx-radio-button');
    this.addWidgetDef(DpxFormDraft, 'form[data-save-draft]');
    this.addWidgetDef(DpxCustomFieldAttach, '.dpx-custom-field-attach');
    this.addWidgetDef(DpxDoubleSubmitPrevention, '[type="submit"]');

    if (this.$element.is('form')) {
      PortalFormWidget.initForms(this.$element);
    } else {
      PortalFormWidget.initForms(this.$element.find('form'));
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
