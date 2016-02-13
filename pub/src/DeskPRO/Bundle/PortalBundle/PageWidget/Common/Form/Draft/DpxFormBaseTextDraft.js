import { DpxFormFieldDraft } from './DpxFormFieldDraft';

export class DpxFormBaseTextDraft extends DpxFormFieldDraft {

  getValue() {
    return this.$element.val();
  }

  restoreValue(storedValue) {
    this.$element.val(storedValue).trigger('change');
  }
}
