import { DpxFormFieldDraft } from './DpxFormFieldDraft';

export class DpxFormRadioDraft extends DpxFormFieldDraft {

  addListeners() {
    this.$element.on('change blur', () => this.update());
  }

  getValue() {
    return this.$element.val();
  }

  setValue(val) {
    if (val === this.$element.val()) {
      this.$element.prop('checked', true).trigger('change');
    }
  }
}
