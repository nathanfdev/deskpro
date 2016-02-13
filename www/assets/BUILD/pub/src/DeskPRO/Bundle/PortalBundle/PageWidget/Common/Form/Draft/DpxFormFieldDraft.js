import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';

export class DpxFormFieldDraft extends PageWidget {

  onUpdate = (...args) => {
    const formDrafts = this.parent.getFormDrafts();
    const name = this.getName();
    const value = this.getValue(...args);

    if (value) {
      formDrafts[name] = value;
    } else {
      if (formDrafts.hasOwnProperty(name)) {
        delete formDrafts[name];
      }
    }

    this.parent.updateFormDrafts(formDrafts);
  };

  renderWidget() {
    if (!this.getName()) {
      return;
    }

    const storedValue = this.getStoredValue();
    if (storedValue) {
      setTimeout(() => this.restoreValue(storedValue), 0);
    }

    this.addListeners();
  }

  getName() {
    return this.$element.attr('name');
  }

  getStoredValue() {
    return this.parent.getFormDrafts()[this.getName()];
  }
}
