import React from 'react';
import jQuery from 'jquery';

export class WarningMajor extends React.Component {

  componentDidMount() {
    const $container = jQuery('.dpw-login-warning-major');
    const height = (parseInt($container.css('height').replace(/px/, ''), 10) * -1) + 'px';

    $container.css('top', height);
  }

  render() {
    return (
      <div className="dpw-login-warning-major">
        <h1>Too many login attempts!</h1>
        <p>At your next failed login attempt, your account will be temporarily locked for security. Please check your login details carefully.</p>
      </div>
    );
  }
}
