import React, { PropTypes } from 'react';

export class Options extends React.Component {

  static propTypes = {
    checked: PropTypes.bool.isRequired,
    onChange: PropTypes.func.isRequired
  };

  render() {
    const { checked, onChange } = this.props;

    return (
      <div className="dpw-login-form-options">
        <a href="#" className="password-reminder">Forgotten your password?</a>

        <span className="dpw-login-form-remember-me" onClick={onChange}>
          {checked && <span className="dpw--checkbox-boxy"><i className="fa fa-check"></i></span>}
          <span>Remember me</span>
        </span>
      </div>
    );
  }
}
