import _ from 'lodash';
import $ from 'jquery';
import EventEmitter from 'eventemitter2';

/**
 * fieldFilter takes the follow parameters:
 * - {Array} fieldNames     An array of fields this object knows about
 * - {Array} currentFields  An array of fields currently displayed on the form
 * - {DynamicForm} inst     Instance of the DynamicForm class
 *
 * fieldFilter must return an array or Set of field names to activate (in order).
 *
 * @option {HTMLElement/jQuery} formEl           The main form element. Rows will be rendered in here.
 * @option {HTMLElement/jQuery} tplEl            A template container containing elements that will be moved into the form
 * @option {Function}           fieldFilter      A function that should return fields that should show in the form.
 * @option {String}             widgetClassName  The classname used to indicate an element is a form widget. Default: deskpro-form-widget
 * @option {Boolean}            runInitUpdate    Run the update() method upon construction. Default: true
 * @option {Array}              alwaysFields     Array of fields that are always added to the form, even if they are missing from the filter.
 */
export class DynamicForm {
  constructor(options) {
    options = _.defaults(options, {
      widgetClassName: 'deskpro-form-widget',
      runInitUpdate:   true,
      alwaysFields:    []
    });

    this.$formEl = $(options.formEl);
    this.$tplEl = $(options.tplEl);
    this.fieldFilter = options.fieldFilter;
    this.widgetClassName = options.widgetClassName;
    this.alwaysFields = options.alwaysFields;
    this.ee = new EventEmitter({
      maxListeners: 0
    });

    this.$tplEl.detach();

    this.fields = new Map();
    this.currentFields = [];

    this.$formEl.find('.' + this.widgetClassName).each((x, el) => {
      el = $(el);
      const name = el.data('field');
      if (name && !el.hasClass('as-static') && !this.fields.has(name)) {
        this.currentFields.push(name);
        this.fields.set(name, el);
      }
    });

    this.$tplEl.find('.' + this.widgetClassName).each((x, el) => {
      el = $(el);
      const name = el.data('field');
      if (name && !el.hasClass('as-static') && !this.fields.has(name)) {
        this.fields.set(name, el);
      }
    });

    if (options.onInit) {
      this.ee.on('init', options.onInit);
    }
    if (options.onFieldsUpdated) {
      this.ee.on('fieldsUpdated', options.onFieldsUpdated);
    }
    if (options.onPostUpdate) {
      this.ee.on('postUpdate', options.onPostUpdate);
    }

    this.fieldNames = Array.from(this.fields.keys());
    this.fieldElements = Array.from(this.fields.values());

    console.log('[DynamicForm] <constructor> Field Names: %o -- Current Fields: %o', this.fieldNames, this.currentFields);

    if (options.runInitUpdate) {
      this.update();
    }

    this.ee.emit('init', { inst: this });
  }

  /**
   * Get a map of name=>el of fields this object handles.
   *
   * @returns {Map}
   */
  getFields() {
    return this.fields;
  }

  /**
   * Gets an array of field names this object handles.
   *
   * @returns {Array}
   */
  getFieldNames() {
    return this.fieldNames;
  }

  /**
   * Gets an array of field elements this object handles.
   *
   * @return {Array}
   */
  getFieldElements() {
    return this.fieldElements;
  }

  /**
   * Gets the currently active field set.
   * @returns {Array}
   */
  getFieldSet() {
    return this.currentFields;
  }

  getHiddenFields() {
    return this.fieldNames.filter(name => this.currentFields.indexOf(name) === -1);
  }

  /**
   * Sets the current field set.
   *
   * @param {Array} fields
   */
  setFieldSet(fields) {
    let evData = { inst: this, cancel: false, fields };
    this.ee.emit('preFieldsUpdated', evData);
    if (evData.cancel) return;

    const oldFields = this.currentFields;
    this.currentFields = this.resolveFields(evData.fields);

    console.log('[DynamicForm] <setFieldSet> Fields: %o', fields);

    this.$formEl.find('.' + this.widgetClassName).not('.as-static').detach();

    let insertPoint = this.$formEl.find('.dynamic-fields-container');
    if (!insertPoint[0]) {
      insertPoint = this.$formEl;
    }

    this.currentFields.map((name) => {
      if (this.fields.has(name)) {
        const $el = this.fields.get(name);
        if (!$el) {
          console.warn('Unknown field: %s', name);
        }
        $el.detach().appendTo(insertPoint);
      }
    });

    // unset values of hidden fields
    oldFields.forEach((name) => {
      if (this.currentFields.indexOf(name) !== -1) {
        return;
      }

      const $el = this.fields.get(name);
      if (!$el) {
        console.warn('Unknown field: %s', name);
      }

      $el.find('input[type=text], textarea, select').val('').trigger('change');
      $el.find('input[type=checkbox]').attr('checked', false).trigger('change');
    });

    evData = { inst: this };
    this.ee.emit('fieldsUpdated', evData);
  }

  /**
   * @param {Array} fields
   * @returns {Array}
   */
  resolveFields(fields) {
    if (this.alwaysFields.length) {
      fields = _.union(fields, this.alwaysFields);
    }

    fields = _.uniq(fields);

    return fields;
  }

  /**
   * Update the form.
   */
  update() {
    let didChange;

    do {
      didChange = false;

      if (this._firePreUpdate().cancel) {
        break;
      }

      const r = this._fireUpdateFields(this.fieldFilter(this.fieldNames, this));
      if (r.cancel) {
        break;
      }

      const newFields = this.resolveFields(r.newFields);
      if (newFields.length !== this.currentFields.length) {
        didChange = true;
      } else {
        for (let i = 0; i < newFields.length; i++) {
          if (newFields[i] !== this.currentFields[i]) {
            didChange = true;
            break;
          }
        }
      }

      if (didChange) {
        this.setFieldSet(newFields);
      } else {
        console.log('[DynamicForm] <update> No change. Fields: %o', this.currentFields);
      }

      this._firePostUpdate(didChange);
    } while (didChange);
  }

  _firePreUpdate() {
    const evData = { inst: this, cancel: false };
    this.ee.emit('preUpdate', evData);

    return evData;
  }

  _firePostUpdate(didChange) {
    const evData = { inst: this, didChange };
    this.ee.emit('postUpdate', evData);

    return evData;
  }

  _fireUpdateFields(newFields) {
    const evData = { inst: this, cancel: false, newFields, currentFields: this.currentFields };
    this.ee.emit('updateFields', evData);

    return evData;
  }
}
