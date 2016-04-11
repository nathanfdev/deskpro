import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { openLoginWindow } from '../../../../Application/Actions/dpWindowActions';
import { getSession } from '../../../../Application/Actions/bootstrapActions';
import { liveDemoSelector } from '../../../../Application/Selectors/dpWindow';
import { widgetSessionIsLoginSelector } from '../../../../Application/Selectors/bootstrap';
import { history } from '../../../../../Services/history';

@connect(state => ({
  isLogin:  widgetSessionIsLoginSelector(state),
  liveDemo: liveDemoSelector(state)
}))
export class ChatLoginContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func,
    liveDemo: PropTypes.bool,
    isLogin:  PropTypes.bool
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

    const { liveDemo, dispatch } = this.props;
    if (liveDemo) {
      return;
    }

    dispatch(openLoginWindow());
  };

  pollingRequest = () => {
    const { liveDemo, dispatch } = this.props;
    if (liveDemo) {
      return;
    }

    const onResponse = () => {
      if (!this.mounted) {
        return;
      }

      setTimeout(() => this.pollingRequest(), 3000);
    };

    const promise = dispatch(getSession());
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
