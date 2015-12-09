import React, { PropTypes } from 'react';

export function getFormErrors(response, name) {
  const errors = response && response.fields || {};
  return errors[name] ? errors[name].errors : [];
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
        {getFormErrors(errors, name).map((error, index) => <li key={index}>{error.message}</li>)}
      </ul>
    );
  }
}
