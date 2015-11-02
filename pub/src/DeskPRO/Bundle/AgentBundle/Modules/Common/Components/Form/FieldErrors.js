import React, { PropTypes } from 'react';

export class FieldErrors extends React.Component {

  static propTypes = {
    errors: PropTypes.object,
    name: PropTypes.string,
    className: PropTypes.string
  };

  render() {
    const { errors, name, className = 'error' } = this.props;
    const fieldsErrors = errors && errors.fields || {};

    let key;
    let fieldErrors = [];
    for (key in fieldsErrors) {
      if (fieldsErrors.hasOwnProperty(key)) {
        if (key === name) {
          fieldErrors = fieldsErrors[key].errors || [];
        }
      }
    }

    return (
      <ul className={className}>
        {fieldErrors.map((error, index) => (<li key={index}>{error.message}</li>))}
      </ul>
    );
  }
}
