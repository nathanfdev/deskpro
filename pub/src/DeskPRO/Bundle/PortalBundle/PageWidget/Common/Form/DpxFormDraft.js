import $ from 'jquery';
import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';

function updateDrafts(obj) {
  window.localStorage.form_drafts = JSON.stringify(obj);
}

function getDrafts() {
  if (window.localStorage.form_drafts) {
    return $.parseJSON(window.localStorage.form_drafts);
  }

  return {};
}

class DpxFormFieldDraft extends PageWidget {

  renderWidget() {
    if (!this.getName()) {
      return;
    }

    this.restoreValue();
    this.addListeners();
  }

  restoreValue() {
    const formDrafts = this.parent.getFormDrafts();
    if (formDrafts[this.getName()]) {
      this.setValue(formDrafts[this.getName()]);
    }
  }

  getName() {
    return this.$element.attr('name');
  }

  update() {
    const formDrafts = this.parent.getFormDrafts();
    formDrafts[this.getName()] = this.getValue();

    this.parent.updateFormDrafts(formDrafts);
  }
}

class DpxFormTextDraft extends DpxFormFieldDraft {

  addListeners() {
    this.$element.on('change blur keyup', () => this.update());
  }

  getValue() {
    return this.$element.val();
  }

  setValue(val) {
    this.$element.val(val).trigger('change');
  }
}

class DpxFormCheckboxDraft extends DpxFormFieldDraft {

  addListeners() {
    this.$element.on('change blur', () => this.update());
  }

  getValue() {
    return this.$element.is(':checked');
  }

  setValue(val) {
    this.$element.prop('checked', !!val).trigger('change');
  }
}

class DpxFormSelectDraft extends DpxFormFieldDraft {

  addListeners() {
    this.$element.on('click change blur', () => this.update());
  }

  getValue() {
    return this.$element.val();
  }

  setValue(val) {
    this.$element.val(val).trigger('change');
  }
}

export class DpxFormDraft extends PageWidget {

  init() {
    this.addWidgetDef(DpxFormTextDraft, 'input[type="text"], input[type="email"], textarea');
    this.addWidgetDef(DpxFormCheckboxDraft, 'input[type="checkbox"], input[type="radio"]');
    this.addWidgetDef(DpxFormSelectDraft, 'select');
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
    const clearDraft = () => {
      delete window.localStorage.form_drafts;
    };

    this.$element.on('submit', clearDraft);
    this.$element.on('reset', clearDraft);

    const $formSubmit = this.$element.find('input[type="submit"]:visible, button[type="submit"]:visible');
    $('<button type="reset">Reset</button>').insertAfter($formSubmit);
  }
}
