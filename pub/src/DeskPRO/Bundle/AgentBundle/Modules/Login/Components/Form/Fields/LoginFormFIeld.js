import React, { PropTypes } from 'react';
import jQuery from 'jquery';

export class LoginFormField extends React.Component {

  static propTypes = {
    label: PropTypes.string.isRequired,
    iconClass: PropTypes.string.isRequired,
    children: PropTypes.node,
    hasError: PropTypes.bool
  };

  componentDidMount() {
    const $errorContainer = jQuery('.error-container');
    const errorWidth = (parseInt($errorContainer.css('width').replace(/px/, ''), 10) * -1 + 10) + 'px';

    $errorContainer.css('right', errorWidth);
  }

  render() {
    const { label, iconClass, children, hasError } = this.props;
    const iconClasses = ['fa', iconClass];

    const fieldClasses = ['dpw-login-form-container'];
    if (hasError) {
      fieldClasses.push('error');
    }

    return (
      <div className={fieldClasses.join(' ')}>
        <div className="dpw-login-form-warning-container error-container">
          <i className="fa fa-exclamation-triangle"></i> <span>Looks like this isn't the correct password</span>
        </div>

        <label>{label}</label>
        <span className="dpw-login-form-input-icon"><i className={iconClasses.join(' ')}></i></span>

        {children}
      </div>
    );
  }
}
