import { DpxFormBaseTextDraft } from './DpxFormBaseTextDraft';

export class DpxFormSelectDraft extends DpxFormBaseTextDraft {

  addListeners() {
    this.$element.on('click change blur', this.onUpdate);
  }
}
