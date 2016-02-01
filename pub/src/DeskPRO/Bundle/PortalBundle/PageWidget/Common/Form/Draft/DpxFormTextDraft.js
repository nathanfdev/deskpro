import { DpxFormFieldDraft } from './DpxFormFieldDraft';

export class DpxFormTextDraft extends DpxFormFieldDraft {

  addListeners() {
    this.$element.on('change blur keyup', this.onUpdate);
  }

  getValue() {
    return this.$element.val();
  }

  restoreValue(storedValue) {
    this.$element.val(storedValue).trigger('change');
  }
}
