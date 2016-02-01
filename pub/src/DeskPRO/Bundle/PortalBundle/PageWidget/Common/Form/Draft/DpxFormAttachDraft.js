import { DpxFormFieldDraft } from './DpxFormFieldDraft';

export class DpxFormAttachDraft extends DpxFormFieldDraft {

  addListeners() {
    this.$element.on('blobs', this.onUpdate);
  }

  getValue(event, blobs) {
    return blobs;
  }

  restoreValue(storedValue) {
    if (storedValue) {
      this.$element.trigger('setBlobs', [storedValue]);
    }
  }
}
