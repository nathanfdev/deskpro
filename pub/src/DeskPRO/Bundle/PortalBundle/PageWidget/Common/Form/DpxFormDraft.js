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

class FormFieldSaveDraft extends PageWidget {

  renderWidget() {
    const $el = this.$element;

    if (!$el.attr('name')) {
      return;
    }

    this.restoreValue();

    if ($el.is('input[type="checkbox"], input[type="radio"]')) {
      $el.on('click change blur', () => this.updateDraft());
    } else if ($el.is('input, textarea')) {
      $el.on('change blur keyup', () => this.updateDraft());
    } else if ($el.is('select')) {
      $el.on('change blur', () => this.updateDraft());
    }
  }

  restoreValue() {
    const formDrafts = this.parent.getFormDrafts();
    if (formDrafts[this.getName()]) {
      this.setValue(formDrafts[this.getName()]);
    }
  }

  updateDraft() {
    const formDrafts = this.parent.getFormDrafts();
    formDrafts[this.getName()] = this.getValue();

    this.parent.updateFormDrafts(formDrafts);
  }

  getName() {
    return this.$element.attr('name');
  }

  getValue() {
    const $el = this.$element;

    if ($el.is('input[type="checkbox"], input[type="radio"]')) {
      return $el.is(':checked');
    } else if ($el.is('input, textarea')) {
      return $el.val();
    } else if ($el.is('select')) {
      return $el.val();
    }
  }

  setValue(val) {
    const $el = this.$element;

    if ($el.is('input[type="checkbox"], input[type="radio"]')) {
      $el.prop('checked', !!val).trigger('change');
    } else if ($el.is('input, textarea')) {
      $el.val(val).trigger('change');
    } else if ($el.is('select')) {
      $el.val(val).trigger('change');
    }
  }
}

export class DpxFormDraft extends PageWidget {

  init() {
    this.addWidgetDef(FormFieldSaveDraft, 'input[type="text"], input[type="email"], input[type="checkbox"], input[type="radio"], textarea, select');
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
