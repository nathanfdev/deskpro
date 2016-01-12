import React, { PropTypes } from 'react';
import { connect } from 'react-redux';

@connect()
export class ChatLoginContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func
  };

  onOpenLoginPopup = event => {
    event.preventDefault();
    console.log('on open popup');
  };

  render() {
    return (
      <div className="dpdesignportal-chat-require-login">
        Please log in or register to start a chat.
        <button onClick={this.onOpenLoginPopup}>Log In or Register</button>
      </div>
    );
  }
}
