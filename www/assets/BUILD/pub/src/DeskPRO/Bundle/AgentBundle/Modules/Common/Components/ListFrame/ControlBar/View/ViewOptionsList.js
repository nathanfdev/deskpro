import PropTypes from 'prop-types';
import React, { Component } from 'react';
import Immutable from 'immutable';
import { ViewFieldContainer } from './ViewFieldContainer';

export class ViewOptionsList extends Component {

  static propTypes = {
    fields:                PropTypes.object.isRequired,
    toggleFieldVisibility: PropTypes.func,
    changeFieldOrder:      PropTypes.func,
    type:                  PropTypes.string.isRequired
  };

  shouldComponentUpdate(props) {
    return !Immutable.is(props.fields, this.props.fields);
  }

  toggleVisibility = index => {
    const { toggleFieldVisibility, type } = this.props;
    if (!toggleFieldVisibility) return;
    toggleFieldVisibility({ type, index });
  };

  changeOrder = (from, to) => {
    const { changeFieldOrder, type } = this.props;
    if (!changeFieldOrder) return;
    changeFieldOrder({ type, from, to });
  };

  renderField = (field, index) => {
    if (!field.get('required')) {
      const { type } = this.props;
      return (
        <ViewFieldContainer
          key={index}
          index={index}
          field={field}
          type={type}
          toggleVisibility={this.toggleVisibility}
          changeOrder={this.changeOrder}
        />
      );
    }
    return null;
  };

  render() {
    const { fields } = this.props;

    return (
      <div className="dpw-navigation-dropdown-column-list">
        <ul>
          {fields.map((field, index) => this.renderField(field, index))}
        </ul>
      </div>
    );
  }
}
