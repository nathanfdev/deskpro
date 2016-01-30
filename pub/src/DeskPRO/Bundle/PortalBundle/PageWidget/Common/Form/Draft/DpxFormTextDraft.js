import { DpxFormFieldDraft } from './DpxFormFieldDraft';

export class DpxFormTextDraft extends DpxFormFieldDraft {

  addListeners() {
    this.$element.on('change blur keyup', () => this.update());
  }

  getValue() {
    return this.$element.val();
  }

  setValue(storedValue) {
    this.$element.val(storedValue).trigger('change');
  }
}
