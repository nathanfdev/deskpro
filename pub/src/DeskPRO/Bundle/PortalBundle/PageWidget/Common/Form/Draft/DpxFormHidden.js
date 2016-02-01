import { DpxFormFieldDraft } from './DpxFormFieldDraft';

export class DpxFormHidden extends DpxFormFieldDraft {

  addListeners() {
    this.$element.on('change', this.onUpdate);
  }

  getValue() {
    return this.$element.val();
  }

  restoreValue(storedValue) {
    this.$element.val(storedValue).trigger('change');
  }
}
