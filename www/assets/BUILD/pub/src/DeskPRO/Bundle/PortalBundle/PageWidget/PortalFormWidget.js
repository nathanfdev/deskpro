import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';
import { DpxSelectBox } from './Common/Form/DpxSelectBox';
import { DpxMultipleSelectBox } from './Common/Form/DpxMultipleSelectBox';
import { DpxCheckboxGroup } from './Common/Form/DpxCheckboxGroup';
import { DpxDateWidget } from './Common/Form/DpxDateWidget';
import { DpxAttach } from './Common/Form/DpxAttach';
import { DpxRte } from './Common/Form/DpxRte';
import { DpxRadio } from './Common/Form/DpxRadio';
import { DpxFormDraft } from './Common/Form/Draft/DpxFormDraft';

export class PortalFormWidget extends PageWidget {

  init() {
    this.addWidgetDef(DpxDateWidget, '.dpx-date');
    this.addWidgetDef(DpxDateWidget, '.dpx-date-time');
    this.addWidgetDef(DpxSelectBox, 'select[dpx-select]');
    this.addWidgetDef(DpxMultipleSelectBox, 'select[dpx-select-multiple]');
    this.addWidgetDef(DpxCheckboxGroup, '.dpx-checkbox-group');
    this.addWidgetDef(DpxAttach, '.dpx-attach');
    this.addWidgetDef(DpxRte, '[data-rte]');
    this.addWidgetDef(DpxRadio, '.dpx-radio-button');
    this.addWidgetDef(DpxFormDraft, 'form[data-save-draft]');

    if (this.$element.is('form')) {
      this.initForms(this.$element);
    } else {
      this.initForms(this.$element.find('form'));
    }
  }

  initForms($forms) {
    // Disable pressing enter from submitting forms by accident
    $forms.find('input, select').not('[type="submit"], [type="reset"], [type="button"]').on('keyup keypress', function(ev) {
      const keyCode = ev.keyCode || ev.which;
      if (keyCode === 13) {
        ev.preventDefault();
        return false;
      }
    });
  }
}
