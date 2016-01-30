import { DpxFormFieldDraft } from './DpxFormFieldDraft';

export class DpxFormCheckboxDraft extends DpxFormFieldDraft {

  addListeners() {
    this.$element.on('change blur', () => this.update());
  }

  getValue() {
    const $el = this.$element;
    const isChecked = $el.is(':checked');
    const value = $el.val();

    // Handle checkbox groups
    if ($el.is('[value]')) {
      const storedValue = this.getStoredValue();

      if (Array.isArray(storedValue)) {
        const index = storedValue.indexOf(value);

        if (isChecked) {
          if (index === -1) {
            storedValue.push(value);
          }
        } else {
          storedValue.splice(index, 1);
        }

        return storedValue;
      } else if (isChecked) {
        if (storedValue && storedValue !== value) {
          return [storedValue, value];
        }

        return value;
      }

      return null;
    }

    return isChecked;
  }

  setValue(storedValue) {
    const $el = this.$element;
    const value = $el.val();

    let checked = false;
    if ($el.is('[value]')) {
      if (Array.isArray(storedValue)) {
        checked = storedValue.indexOf(value) !== -1;
      } else {
        checked = storedValue === value;
      }
    } else {
      checked = !!storedValue;
    }


    this.$element.prop('checked', checked).trigger('change');
  }
}
