/*jshint node:true */

'use strict';

const React = require('react');
const Formsy = require('formsy-react');
const ComponentMixin = require('./mixins/component');
const jQuery = require('jquery');

const CheckboxGroupDeskPRO = React.createClass({

  propTypes: {
    name: React.PropTypes.string.isRequired,
    onChange: React.PropTypes.func,
    options: React.PropTypes.array.isRequired
  },

  mixins: [Formsy.Mixin, ComponentMixin],

  getDefaultProps: function() {
    return {
      label: '',
      help: null
    };
  },

  changeCheckbox: function(event) {
    const value = [];
    const target = event.currentTarget;

    // target.checked = (typeof target.checked === 'undefined' || target.checked === false);
    jQuery(target).data('checked', !jQuery(target).data('checked'));

    this.props.options.forEach(option => {
      if (jQuery(this.refs[option.value]).data('checked')) {
        value.push(option.value);
      }
    });

    this.setValue(value);
    this.props.onChange(this.props.name, value);
  },

  renderElement: function() {
    const _this = this;
    var controls = this.props.options.map((checkbox, key) => {
      var checked = (typeof _this.getValue() !== 'undefined' && _this.getValue().indexOf(checkbox.value) !== -1);
      const disabled = _this.isFormDisabled() || checkbox.disabled || _this.props.disabled;
      const checkboxClass = checked ? 'checkbox-button checked' : 'checkbox-button';
      return (
        <li key={key}><a
        checked={checked}
        data-checked={checked}
        disabled={disabled}
        href="#"
        className={checkboxClass}
        onClick={_this.changeCheckbox}
        ref={checkbox.value}
        >
          <span className="checkbox">
            <i className="fa fa-check"/>
          </span>
          <span className="name">{checkbox.label}</span>
        </a></li>
      );
    });

    return <ul>{controls}</ul>;
  },

  render: function() {
    if (this.getLayout() === 'elementOnly') {
      return (
        <div>{this.renderElement()}</div>
      );
    }

    return this.renderElement();
  }
});

module.exports = CheckboxGroupDeskPRO;
