import React, { PropTypes } from 'react';
import jQuery from 'jquery';
import Positioned from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Positioned';

export class FieldWrapper extends React.Component {

  static propTypes = {
    label: PropTypes.string.isRequired,
    iconClass: PropTypes.string.isRequired,
    children: PropTypes.node,
    hasError: PropTypes.bool
  };

  render() {
    const { label, iconClass, children, hasError } = this.props;
    const iconClasses = ['fa', iconClass];

    const fieldClasses = ['dpw-login-form-container'];
    if (hasError) {
      fieldClasses.push('error');
    }

    return (
      <div className={fieldClasses.join(' ')}>
        <Positioned
          isOpen={hasError}
          positionTarget={this}
          positionAt="right top"
          positionMy="left center">

          <div className="dpw-login-form-warning-container error-container">
            <i className="fa fa-exclamation-triangle"></i> <span>Looks like this isn't the correct password</span>
          </div>
        </Positioned>

        <label>{label}</label>
        <span className="dpw-login-form-input-icon"><i className={iconClasses.join(' ')}></i></span>

        {children}
      </div>
    );
  }
}
