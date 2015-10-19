import React from 'react';

export class TimeLocked extends React.Component {

  render() {
    return (
      <div>
        <div className="lockout-counter"><i className="fa fa-lock"></i> <span>25m : 00s</span></div>
        <h1>You've been locked out due to failed login attempts.!</h1>
        <p>At your next failed login attempt, your account will be temporarily locked for security. Please check your login details carefully.</p>
      </div>
    );
  }
}
