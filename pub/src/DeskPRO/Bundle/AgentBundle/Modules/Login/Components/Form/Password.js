import React from 'react';
import { LoginFormField } from './LoginFormField';
import jQuery from 'jquery';

export class Password extends React.Component {

  componentDidMount() {
    const $warningContainer = jQuery('.warning-container');
    if ($warningContainer) {
      const warningWidth = (parseInt($warningContainer.css('width').replace(/px/, ''), 10) * -1 + 5) + 'px';
      $warningContainer.css('left', warningWidth);
    }
  }

  render() {
    return (
      <LoginFormField iconClass="fa-lock" label="Password" customClass="password-container">
        <div className="dpw-login-form-warning-container warning-container">
          <i className="fa fa-arrow-circle-o-up"></i> <span>Looks like caps lock is on?</span>
        </div>

        <input type="password" />
      </LoginFormField>
    );
  }
}
