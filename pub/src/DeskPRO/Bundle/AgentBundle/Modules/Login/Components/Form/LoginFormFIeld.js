import React, { PropTypes } from 'react';
import jQuery from 'jquery';

export class LoginFormField extends React.Component {

  static propTypes = {
    label: PropTypes.string.isRequired,
    iconClass: PropTypes.string.isRequired,
    children: PropTypes.object.isRequired,
    customClass: PropTypes.string
  };

  componentDidMount() {
    const $errorContainer = jQuery('.error-container');
    const errorWidth = (parseInt($errorContainer.css('width').replace(/px/, ''), 10) * -1 + 10) + 'px';

    $errorContainer.css('right', errorWidth);

    const $warningContainer = jQuery('.warning-container');
    const warningWidth = (parseInt($warningContainer.css('width').replace(/px/, ''), 10) * -1 + 5) + 'px';

    $warningContainer.css('left', warningWidth);
  }

  render() {
    const { label, iconClass, children, customClass } = this.props;
    const iconClasses = ['fa', iconClass];

    const fieldClasses = ['dpw-login-form-container'];
    if (customClass) {
      fieldClasses.push(customClass);
    }

    return (
      <div className={fieldClasses.join(' ')}>
        <div className="dpw-login-form-warning-container error-container">
          <i className="fa fa-exclamation-triangle"></i> <span>Looks like this isn't the correct password</span>
        </div>

        <div className="dpw-login-form-warning-container warning-container">
          <i className="fa fa-arrow-circle-o-up"></i> <span>Looks like caps lock is on?</span>
        </div>

        <label>{label}</label>
        <span className="dpw-login-form-input-icon"><i className={iconClasses.join(' ')}></i></span>

        {children}
      </div>
    );
  }
}
