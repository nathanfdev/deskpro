import $ from 'jquery';
import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';
import { DpxFormTextDraft } from './DpxFormTextDraft';
import { DpxFormCheckboxDraft } from './DpxFormCheckboxDraft';
import { DpxFormRadioDraft } from './DpxFormRadioDraft';
import { DpxFormSelectDraft } from './DpxFormSelectDraft';
import { DpxFormHiddenDraft } from './DpxFormHiddenDraft';
import { DpxFormRteBlobsDraft } from './DpxFormRteBlobsDraft';
import { DpxFormAttachDraft } from './DpxFormAttachDraft';


function updateDrafts(obj) {
  window.localStorage.form_drafts = JSON.stringify(obj);
}

function getDrafts() {
  let object;

  if (window.localStorage.form_drafts) {
    try {
      object = $.parseJSON(window.localStorage.form_drafts);
    } catch (e) {
      object = {};
    }
  }
  if (typeof object !== 'object') {
    object = {};
  }

  return object;
}

export default class DpxFormDraft extends PageWidget {

  onClearDraft = () => {
    const drafts = getDrafts();
    drafts[this.getFormName()] = {};

    updateDrafts(drafts);
  };

  init() {
    this.addWidgetDef(DpxFormTextDraft, 'input[type="text"], input[type="email"], textarea');
    this.addWidgetDef(DpxFormCheckboxDraft, 'input[type="checkbox"]');
    this.addWidgetDef(DpxFormRadioDraft, 'input[type="radio"]');
    this.addWidgetDef(DpxFormSelectDraft, 'select');
    this.addWidgetDef(DpxFormHiddenDraft, 'input[type="hidden"]');
    this.addWidgetDef(DpxFormRteBlobsDraft, 'textarea[data-rte-field="html"]');
    this.addWidgetDef(DpxFormAttachDraft, '.dpx-attach input[type="file"]');
  }

  getFormName() {
    if (!this.formName) {
      this.formName = this.$element.data('save-draft');
    }

    return this.formName;
  }

  getFormDrafts() {
    return getDrafts()[this.getFormName()] || {};
  }

  updateFormDrafts(newDrafts) {
    const drafts = getDrafts();
    drafts[this.getFormName()] = newDrafts;

    updateDrafts(drafts);
  }

  static resetForm() {
    return confirm(portalPhrases.get('portal.forms.confirm_reset'));
  }

  renderWidget() {
    this.$element.on('submit', this.onClearDraft);
    const $formSubmit = this.$element.find('input[type="submit"]:visible, button[type="submit"]:visible');

    if (!this.options.isWidget) {
      this.$element.on('reset', this.onClearDraft);
      $(`<button type="reset">${portalPhrases.get('portal.forms.label_reset')}</button>`)
        .on('click', DpxFormDraft.resetForm)
        .insertAfter($formSubmit);
    }
  }
}
