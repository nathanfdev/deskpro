import React from 'react';

export class NotAgentWarning extends React.Component {

  render() {
    return (
      <div>
        <h1>Looks like you're registered but not an agent!</h1>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
        <div className="dpw-login-warning-major-buttons">
          <a href="">Visit the user portal</a>
          <a href="">Request an agent account</a>
        </div>
      </div>
    );
  }
}
