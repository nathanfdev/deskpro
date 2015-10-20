import React, { PropTypes } from 'react';

export class FieldWrapper extends React.Component {

  static propTypes = {
    label: PropTypes.string.isRequired,
    children: PropTypes.any.isRequired
  };

  render() {
    const { label, children } = this.props;

    return (
      <div className="bucket">
        <label className="label">{label}:</label>
        {children}
      </div>
    );
  }
}
