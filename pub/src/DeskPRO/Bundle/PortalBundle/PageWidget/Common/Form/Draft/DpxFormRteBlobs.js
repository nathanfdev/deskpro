import { DpxFormFieldDraft } from './DpxFormFieldDraft';

export class DpxFormRteBlobs extends DpxFormFieldDraft {

  addListeners() {
    this.$element.on('blob', this.onUpdate);
  }

  getName() {
    return super.getName() + '_blobs';
  }

  getValue(event, blob) {
    let storedValue = this.getStoredValue();
    if (!storedValue) {
      storedValue = [];
    } else if (!Array.isArray(storedValue)) {
      storedValue = [storedValue];
    }

    storedValue.push(blob);
    return storedValue;
  }

  restoreValue(storedValue) {
    if (storedValue) {
      this.$element.trigger('setBlobs', [storedValue]);
    }
  }
}
