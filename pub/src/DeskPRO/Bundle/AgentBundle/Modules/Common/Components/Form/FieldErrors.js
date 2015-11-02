import React, { PropTypes } from 'react';

export class FieldErrors extends React.Component {

  static propTypes = {
    errors: PropTypes.object,
    name: PropTypes.string
  };

  render() {
    const { errors, name } = this.props;
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
      <ul>
        {fieldErrors.map(error => (<li>{error.message}</li>))}
      </ul>
    );
  }
}
