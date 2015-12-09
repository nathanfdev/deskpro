import React, { PropTypes } from 'react';

export function getErrors(response, name) {
  const errors = response && response.fields || {};
  return errors[name] ? errors[name].errors : [];
}

export function hasErrors(response, name) {
  return getErrors(response, name).length > 0;
}

export class FieldErrors extends React.Component {

  static propTypes = {
    errors: PropTypes.object,
    name: PropTypes.string,
    className: PropTypes.string
  };

  render() {
    const { errors, name, className = 'error' } = this.props;

    return (
      <ul className={className}>
        {getErrors(errors, name).map((error, index) => <li key={index}>{error.message}</li>)}
      </ul>
    );
  }
}
