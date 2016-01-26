import PageWidget from 'DeskPRO/Component/PageWidget/PageWidget';
import DpxSelectBox from 'DeskPRO/Bundle/PortalBundle/PageWidget/Common/Form/DpxSelectBox';
import DpxMultipleSelectBox from 'DeskPRO/Bundle/PortalBundle/PageWidget/Common/Form/DpxMultipleSelectBox';
import DpxCheckboxGroup from 'DeskPRO/Bundle/PortalBundle/PageWidget/Common/Form/DpxCheckboxGroup';
import DpxDateWidget from 'DeskPRO/Bundle/PortalBundle/PageWidget/Common/Form/DpxDateWidget';
import DpxAttach from 'DeskPRO/Bundle/PortalBundle/PageWidget/Common/Form/DpxAttach';
import DpxRte from 'DeskPRO/Bundle/PortalBundle/PageWidget/Common/Form/DpxRte';
import DpxRadio from 'DeskPRO/Bundle/PortalBundle/PageWidget/Common/Form/DpxRadio';
import FormSaveDraft from 'DeskPRO/Bundle/PortalBundle/PageWidget/Common/Form/FormSaveDraft';

export default class PortalFormWidget extends PageWidget {

  init() {
    this.addWidgetDef(DpxDateWidget, '.dpx-date');
    this.addWidgetDef(DpxDateWidget, '.dpx-date-time');
    this.addWidgetDef(DpxSelectBox, 'select[dpx-select]');
    this.addWidgetDef(DpxMultipleSelectBox, 'select[dpx-select-multiple]');
    this.addWidgetDef(DpxCheckboxGroup, '.dpx-checkbox-group');
    this.addWidgetDef(DpxAttach, '.dpx-attach');
    this.addWidgetDef(DpxRte, '[data-rte]');
    this.addWidgetDef(DpxRadio, '.dpx-radio-button');
    this.addWidgetDef(FormSaveDraft, 'form[data-save-draft]');
  }
}
