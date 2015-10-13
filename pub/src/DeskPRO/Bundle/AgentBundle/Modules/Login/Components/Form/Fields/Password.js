import React, { PropTypes } from 'react';
import { LoginFormField } from './LoginFormField';
import jQuery from 'jquery';

export class Password extends React.Component {

  static propTypes = {
    hasError: PropTypes.bool,
    value: PropTypes.string,
    onChange: PropTypes.func.isRequired
  };

  componentDidMount() {
    const $warningContainer = jQuery('.warning-container');
    if ($warningContainer) {
      const warningWidth = (parseInt($warningContainer.css('width').replace(/px/, ''), 10) * -1 + 5) + 'px';
      $warningContainer.css('left', warningWidth);
    }
  }

  render() {
    const { hasError, value, onChange } = this.props;

    return (
      <LoginFormField iconClass="fa-lock" label="Password" hasError={hasError}>
        <div className="dpw-login-form-warning-container warning-container">
          <i className="fa fa-arrow-circle-o-up"></i> <span>Looks like caps lock is on?</span>
        </div>

        <input type="password" placeholder="........." value={value} onChange={onChange} />
      </LoginFormField>
    );
  }
}
