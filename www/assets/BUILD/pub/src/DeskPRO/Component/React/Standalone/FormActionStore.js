import $ from 'jquery';

/**
 * A FormActionStore facilitates communication between
 * a standalone React component and a normal form element on the page.
 *
 * In other words, a React component draws rich UI for a regular form element.
 * For example, replacing a select box with a rich select box. The value from the
 * component is synced with a real form element.
 */
export class FormActionStore {
  constructor(el) {
    this.el = $(el);
    this.listeners = {};
    this.init();
    this.initSync();
  }

  init() {
    this.value = this.readValueFromForm();
  }

  initSync() {
    // Used to notify component
    this.el.on('change', event => {
      const oldValue = this.value;
      const value = this.readValueFromForm();

      this.emit('formChanged', { event: event, value: value, oldValue: oldValue });
    });

    // Component notifying us (via setValue)
    this.on('valueChanged', this.onValueChanged.bind(this));
  }

  on(actionType, cb) {
    if (!this.listeners[actionType]) {
      this.listeners[actionType] = [];
    }

    this.listeners[actionType].push(cb);
  }

  off(actionType, cb) {
    if (!this.listeners[actionType]) {
      return;
    }

    this.listeners[actionType] = this.listeners[actionType].filter(i => i !== cb);
  }

  emit(actionType, data = {}) {
    if (!this.listeners[actionType]) {
      return;
    }

    data.actionType = actionType;
    this.listeners[actionType].forEach(cb => cb(data));
  }

  /**
   * Get the real underlying form element.
   *
   * @returns {jQuery}
   */
  getElement() {
    return this.el;
  }

  /**
   * Called when valueChanged event is fired (from the component).
   * You should use this to sync the value back to the form.
   *
   * @param {Object} data Has a `value` property.
   */
  onValueChanged(data) {
    this.el.val(data.value);
  }

  /**
   * This is called to get the initial value, and also when
   * responding ot 'change' events.
   *
   * @returns {*}
   */
  readValueFromForm() {
    return this.el.val();
  }

  /**
   * Sets the value. This may or may not be the same as the underlying form.
   *
   * @param value
   */
  setValue(value) {
    const oldValue = this.value;
    this.value = value;
    this.emit('valueChanged', { value: value, oldValue: oldValue });
  }

  /**
   * Gets the current value. This may or may not be the same as the underlying form.
   *
   * @returns {*}
   */
  getValue() {
    return this.value;
  }
}