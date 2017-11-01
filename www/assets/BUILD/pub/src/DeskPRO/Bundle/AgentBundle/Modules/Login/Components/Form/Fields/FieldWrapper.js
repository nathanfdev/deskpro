import PropTypes from 'prop-types';
import React from 'react';
import { Simple } from 'DeskPRO/Component/Positioned/Simple';
import classNames from 'classnames';
import { hasErrors, getLastError } from 'DeskPRO/Component/Form/FormErrors';

export class FieldWrapper extends React.Component {

  static propTypes = {
    label:     PropTypes.string.isRequired,
    iconClass: PropTypes.string.isRequired,
    children:  PropTypes.node,
    field:     PropTypes.string,
    errors:    PropTypes.object
  };

  render() {
    const { label, iconClass, children, field, errors } = this.props;

    return (
      <div className={classNames('dpw-login-form-container', { 'error': hasErrors(errors, field) })}>
        <Simple
          isOpen={hasErrors(errors, field)}
          positionTarget={this}
          positionAt="right top"
          positionMy="left center"
        >

          <div className="dpw-login-form-warning-container error-container">
            <i className="fa fa-exclamation-triangle"></i> <span>{getLastError(errors, field)}</span>
          </div>
        </Simple>

        <label>{label}</label>
        <span className="dpw-login-form-input-icon"><i className={classNames('fa', iconClass)}></i></span>

        {children}
      </div>
    );
  }
}
