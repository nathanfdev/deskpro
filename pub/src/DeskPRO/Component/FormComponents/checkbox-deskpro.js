/*jshint node:true */

'use strict';

var React = require('react');
var Formsy = require('formsy-react');
var ComponentMixin = require('./mixins/component');
var Row = require('./row');

var CheckboxDeskPRO = React.createClass({

    mixins: [Formsy.Mixin, ComponentMixin],

    getDefaultProps: function() {
        return {
            label: '',
            rowLabel: '',
            value: false
        };
    },

    changeValue: function(event) {
        let target = event.currentTarget;
        let newValue = !target.checked;
        this.setValue(newValue);
        this.props.onChange(this.props.name, newValue);
    },

    renderElement: function() {

        let checkboxClass = this.getValue() ? "checkbox checked" : "checkbox";

        return (
                <a
                    {...this.props}
                    checked={this.getValue() === true}
                    disabled={this.isFormDisabled() || this.props.disabled}
                    href="#"
                    className="checkbox-button"
                    onClick={this.changeValue}
                >
                    <span className={checkboxClass}>
                        <i className="fa fa-check"/>
                    </span>
                    <span className="name">{this.props.children}</span>
                </a>
        );
    },

    render: function() {
        return this.renderElement();
    }
});

module.exports = CheckboxDeskPRO;
