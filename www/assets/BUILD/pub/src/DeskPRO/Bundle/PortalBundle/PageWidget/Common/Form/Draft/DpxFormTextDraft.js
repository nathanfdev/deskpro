import { DpxFormBaseTextDraft } from './DpxFormBaseTextDraft';

export class DpxFormTextDraft extends DpxFormBaseTextDraft {

  addListeners() {
    this.$element.on('change keyup', this.onUpdate);
  }
}
