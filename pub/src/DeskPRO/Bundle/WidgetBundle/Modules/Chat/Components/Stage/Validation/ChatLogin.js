import React, { PropTypes } from 'react';
import { openLoginWindow } from '../../../../Application/Actions/dpWindowActions';

export class ChatLogin extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func
  };

  onOpenLoginPopup = event => {
    event.preventDefault();
    openLoginWindow();
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
