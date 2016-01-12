import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { openLoginWindow } from '../../../../Application/Actions/dpWindowActions';
import { getSession } from '../../../../Application/Actions/bootstrapActions';
import { widgetSessionIsLoginSelector } from '../../../../Application/Selectors/bootstrap';
import history from '../../../../../Services/history';

@connect(state => ({
  isLogin: widgetSessionIsLoginSelector(state)
}))
export class ChatLoginContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func,
    isLogin: PropTypes.bool
  };

  componentDidMount() {
    this.mounted = true;
    this.checkLogin();
    this.pollingRequest();
  }

  componentDidUpdate() {
    this.checkLogin();
  }

  componentWillUnmount() {
    this.mounted = false;
  }

  onOpenLoginPopup = event => {
    event.preventDefault();
    this.props.dispatch(openLoginWindow());
  };

  pollingRequest = () => {
    const onResponse = () => {
      if (!this.mounted) {
        return;
      }

      setTimeout(() => this.pollingRequest(), 3000);
    };

    const promise = this.props.dispatch(getSession());
    promise.then(onResponse, onResponse);
  };

  checkLogin() {
    if (this.props.isLogin) {
      history.replace('/chat/begin/simple');
    }
  }

  render() {
    return (
      <div className="dpdesignportal-chat-require-login">
        Please log in or register to start a chat.
        <button onClick={this.onOpenLoginPopup}>Log In or Register</button>
      </div>
    );
  }
}
