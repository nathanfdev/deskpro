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
    const storedValue = this.getStoredValue();
    if (storedValue) {
      this.setValue(storedValue);
    }
  }

  getName() {
    return this.$element.attr('name');
  }

  getStoredValue() {
    return this.parent.getFormDrafts()[this.getName()];
  }

  update() {
    const formDrafts = this.parent.getFormDrafts();
    formDrafts[this.getName()] = this.getValue();

    this.parent.updateFormDrafts(formDrafts);
  }
}
