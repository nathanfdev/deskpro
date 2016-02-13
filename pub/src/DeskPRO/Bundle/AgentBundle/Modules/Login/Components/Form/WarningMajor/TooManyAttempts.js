import React from 'react';

export class TooManyAttempts extends React.Component {

  render() {
    return (
      <div>
        <h1>Too many login attempts!</h1>
        <p>At your next failed login attempt, your account will be temporarily locked for security. Please check your login details carefully.</p>
      </div>
    );
  }
}
