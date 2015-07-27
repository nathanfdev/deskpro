/*jshint node:true */

'use strict';

var React = require('react');
var Formsy = require('formsy-react');
var ComponentMixin = require('./mixins/component');
var Row = require('./row');

var CheckboxGroupDeskPRO = React.createClass({

    mixins: [Formsy.Mixin, ComponentMixin],

    propTypes: {
        name: React.PropTypes.string.isRequired,
        options: React.PropTypes.array.isRequired
    },

    getDefaultProps: function() {
        return {
            label: '',
            help: null
        };
    },

    changeCheckbox: function(event) {
        let value = [];
        let target = event.currentTarget;

        target.checked = (typeof target.checked === 'undefined' || target.checked === false);

        this.props.options.forEach(function(option, key) {
            if (this.refs[key].getDOMNode().checked) {
                value.push(option.value);
            }

        }.bind(this));
        this.setValue(value);
        this.props.onChange(this.props.name, value);
    },

    renderElement: function() {
        let _this = this;
        var controls = this.props.options.map(function(checkbox, key) {
            var checked = (typeof _this.getValue() !== 'undefined' && _this.getValue().indexOf(checkbox.value) !== -1);
            let disabled = _this.isFormDisabled() || checkbox.disabled || _this.props.disabled;
            let checkboxClass = checked ? "checkbox checked" : "checkbox";
            return (
                <li key={key}><a
                checked={checked}
                disabled={disabled}
                href="#"
                className="checkbox-button"
                onClick={_this.changeCheckbox}
                ref={key}
                >
                    <span className={checkboxClass}>
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
