import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { openLoginWindow } from '../../../../Application/Actions/dpWindowActions';

@connect()
export class ChatLoginContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func
  };

  onOpenLoginPopup = event => {
    event.preventDefault();
    this.props.dispatch(openLoginWindow());
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
