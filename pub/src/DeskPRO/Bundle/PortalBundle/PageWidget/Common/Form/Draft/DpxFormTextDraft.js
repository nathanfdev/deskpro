import { DpxFormFieldDraft } from './DpxFormFieldDraft';

export class DpxFormTextDraft extends DpxFormFieldDraft {

  addListeners() {
    this.$element.on('change blur keyup', () => this.update());
  }

  getValue() {
    return this.$element.val();
  }

  setValue(val) {
    this.$element.val(val).trigger('change');
  }
}
