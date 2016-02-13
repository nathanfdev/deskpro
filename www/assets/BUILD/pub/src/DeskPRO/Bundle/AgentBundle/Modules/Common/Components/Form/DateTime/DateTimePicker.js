import React, { PropTypes } from 'react';
import { AbstractDateTimePicker } from './AbstractDateTimePicker';

export class DateTimePicker extends AbstractDateTimePicker {

  static propTypes = {
    label: PropTypes.string.isRequired,
    value: PropTypes.string,
    className: PropTypes.string.isRequired
  };

  render() {
    const { className, label } = this.props;

    return (
      <div className={className}>
        <label>{label}</label>
        <input type="text" ref="input" />
      </div>
    );
  }
}
