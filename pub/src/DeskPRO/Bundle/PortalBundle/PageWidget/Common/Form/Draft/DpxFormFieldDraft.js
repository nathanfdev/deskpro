import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';

export class DpxFormFieldDraft extends PageWidget {

  renderWidget() {
    if (!this.getName()) {
      return;
    }

    this.restoreValue();
    this.addListeners();
  }

  restoreValue() {
    const formDrafts = this.parent.getFormDrafts();
    if (formDrafts[this.getName()]) {
      this.setValue(formDrafts[this.getName()]);
    }
  }

  getName() {
    return this.$element.attr('name');
  }

  update() {
    const formDrafts = this.parent.getFormDrafts();
    formDrafts[this.getName()] = this.getValue();

    this.parent.updateFormDrafts(formDrafts);
  }
}
