import React, { PropTypes } from 'react';

export class FormItem extends React.Component {

  static propTypes = {
    label: PropTypes.string,
    children: PropTypes.any
  };

  render() {
    const { label, children } = this.props;

    return (
      <label className="inline-form-item">
        <span className="dpdesignportal-form-item-label-title">{label}:</span>
        {children}
      </label>
    );
  }
}
