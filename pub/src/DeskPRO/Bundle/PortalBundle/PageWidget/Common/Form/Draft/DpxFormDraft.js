import $ from 'jquery';
import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';
import { DpxFormTextDraft } from './DpxFormTextDraft';
import { DpxFormCheckboxDraft } from './DpxFormCheckboxDraft';
import { DpxFormRadioDraft } from './DpxFormRadioDraft';
import { DpxFormSelectDraft } from './DpxFormSelectDraft';
import { DpxFormHidden } from './DpxFormHidden';
import { DpxFormRteBlobs } from './DpxFormRteBlobs';

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

export class DpxFormDraft extends PageWidget {

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
    this.addWidgetDef(DpxFormHidden, 'input[type="hidden"]');
    this.addWidgetDef(DpxFormRteBlobs, 'textarea[data-rte-field="html"]');
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

  renderWidget() {
    this.$element.on('submit', this.onClearDraft);
    this.$element.on('reset', this.onClearDraft);

    const $formSubmit = this.$element.find('input[type="submit"]:visible, button[type="submit"]:visible');
    $('<button type="reset">Reset</button>').insertAfter($formSubmit);
  }
}
