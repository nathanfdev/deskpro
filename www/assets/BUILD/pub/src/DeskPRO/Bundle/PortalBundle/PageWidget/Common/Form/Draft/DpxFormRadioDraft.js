import { DpxFormFieldDraft } from './DpxFormFieldDraft';

export class DpxFormRadioDraft extends DpxFormFieldDraft {

  addListeners() {
    this.$element.on('change blur', this.onUpdate);
  }

  getValue() {
    return this.$element.val();
  }

  restoreValue(storedValue) {
    if (storedValue === this.$element.val()) {
      this.$element.prop('checked', true).trigger('change');
    }
  }
}
