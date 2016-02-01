import { DpxFormBaseTextDraft } from './DpxFormBaseTextDraft';

export class DpxFormHiddenDraft extends DpxFormBaseTextDraft {

  addListeners() {
    this.$element.on('change', this.onUpdate);
  }
}
