/*jshint node:true */

'use strict';

var React = require('react');
var Formsy = require('formsy-react');
var ComponentMixin = require('./mixins/component');
var Row = require('./row');

var Checkbox = React.createClass({

    mixins: [Formsy.Mixin, ComponentMixin],

    getDefaultProps: function() {
        return {
            label: '',
            rowLabel: '',
            value: false
        };
    },

    changeValue: function(event) {
        var target = event.currentTarget;
        this.setValue(target.checked);
        this.props.onChange(this.props.name, target.checked);
    },

    renderElement: function() {
        return (<input
                        {...this.props}
                        id={this.getId()}
                        type="checkbox"
                        checked={this.getValue() === true}
                        onChange={this.changeValue}
                        disabled={this.isFormDisabled() || this.props.disabled}
                    />
        );
    },

    render: function() {

        var element = this.renderElement();

        if (this.getLayout() === 'elementOnly') {
            return element;
        }

        return {element};
    }
});

module.exports = Checkbox;
