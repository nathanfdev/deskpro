import { DpxFormFieldDraft } from './DpxFormFieldDraft';

export class DpxFormSelectDraft extends DpxFormFieldDraft {

  addListeners() {
    this.$element.on('click change blur', () => this.update());
  }

  getValue() {
    return this.$element.val();
  }

  setValue(val) {
    this.$element.val(val).trigger('change');
  }
}
