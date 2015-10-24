import React, { PropTypes } from 'react';

export class FieldWrapper extends React.Component {

  static propTypes = {
    label: PropTypes.string.isRequired,
    children: PropTypes.any.isRequired,
    errors: PropTypes.object
  };

  renderErrors() {
    const { errors } = this.props;
    if (errors && errors.errors) {
      return (
        <div className="bucket-column">
          <ul className="error">
            {errors.errors.map(error => (<li>{error.message}</li>))}
          </ul>
        </div>
      );
    }
  }

  render() {
    const { label, children } = this.props;

    return (
      <div className="bucket">
        {label ? (<label className="label">{label}:</label>) : null}
        {children}
        {this.renderErrors()}
      </div>
    );
  }
}
