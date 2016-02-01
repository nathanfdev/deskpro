import { DpxFormFieldDraft } from './DpxFormFieldDraft';

export class DpxFormSelectDraft extends DpxFormFieldDraft {

  addListeners() {
    this.$element.on('click change blur', this.onUpdate);
  }

  getValue() {
    return this.$element.val();
  }

  restoreValue(storedValue) {
    this.$element.val(storedValue).trigger('change');
  }
}
