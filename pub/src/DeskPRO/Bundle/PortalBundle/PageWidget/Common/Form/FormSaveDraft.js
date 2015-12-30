import $ from 'jquery';
import PageWidget from 'DeskPRO/Component/PageWidget/PageWidget';

class FormFieldSaveDraft extends PageWidget {

  renderWidget() {
    const $el = this.$element;

    if (!$el.attr('name')) {
      return;
    }

    this.restoreValue();

    if ($el.is('input[type="checkbox"], input[type="radio"]')) {
      $el.on('click change blur', ev => this.updateDraft());
    } else if ($el.is('input, textarea')) {
      $el.on('change blur keyup', ev => this.updateDraft());
    } else if ($el.is('select')) {
      $el.on('change blur', ev => this.updateDraft());
    }
  }

  restoreValue() {
    const formDrafts = this._getFormDraftsObj();
    if (formDrafts[this.getFormName()][this.getElName()]) {
      this.setValue(formDrafts[this.getFormName()][this.getElName()]);
    }
  }

  updateDraft() {
    const formDrafts = this._getFormDraftsObj();
    formDrafts[this.getFormName()][this.getElName()] = this.getValue();
    this._updateFormDraftsObj(formDrafts);
  }

  getFormName() {
    if (this.formName) {
      return this.formName;
    }

    this.formName = this.$element.closest('form').data('save-draft');
    return this.formName;
  }

  getElName() {
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

  _updateFormDraftsObj(obj) {
    window.localStorage.form_drafts = JSON.stringify(obj);
  }

  _getFormDraftsObj() {
    let o;

    if (window.localStorage.form_drafts) {
      try {
        o = $.parseJSON(window.localStorage.form_drafts);
      } catch (e) {
        o = {};
      }
    } else {
      o = {};
    }

    const formName = this.getFormName();
    if (!o[formName]) {
      o[formName] = {};
    }

    return o;
  }
}

export default class FormSaveDraft extends PageWidget {
  init() {
    this.addWidgetDef(FormFieldSaveDraft, function(widgetClass, $context, parent) {
      const $matches = $context.find('input[type="text"], input[type="email"], input[type="checkbox"], input[type="radio"], textarea, select');
      //todo possibly filter?

      return $matches;
    });
  }

  renderWidget() {
    this.$element.on('submit', (ev) => {
      // clear the draft on submit
      delete window.localStorage.form_drafts;
    });
  }
}
