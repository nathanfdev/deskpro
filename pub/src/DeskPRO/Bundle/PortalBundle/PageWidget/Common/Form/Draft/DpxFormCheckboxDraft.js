import { DpxFormFieldDraft } from './DpxFormFieldDraft';

export class DpxFormCheckboxDraft extends DpxFormFieldDraft {

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
