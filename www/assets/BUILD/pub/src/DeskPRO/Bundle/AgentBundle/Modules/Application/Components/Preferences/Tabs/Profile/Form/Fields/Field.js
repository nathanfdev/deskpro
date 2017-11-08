import PropTypes from 'prop-types';
import React from 'react';
import { FieldErrors } from 'DeskPRO/Component/Form/FormErrors';

export class Field extends React.Component {

  static propTypes = {
    name:     PropTypes.string,
    label:    PropTypes.string,
    children: PropTypes.any.isRequired,
    errors:   PropTypes.array
  };

  renderErrors() {
    const { name, errors } = this.props;
    if (errors) {
      return (
        <div className="bucket-column">
          <FieldErrors name={name} errors={errors} />
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
