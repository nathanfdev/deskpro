import React, { PropTypes } from 'react';
import Frame from 'Ampliflux/common/components/Frame';
import history from '../../../Services/history';

export default class ChatTriggersBody extends React.Component {

  static propTypes = {
    onResize: PropTypes.func
  };

  componentDidMount() {
    this.triggerResize();
  }

  componentDidUpdate() {
    this.triggerResize();
  }

  triggerResize() {
    const { onResize } = this.props;
    if (onResize) {
      window.setTimeout(() => onResize(), 0);
    }
  }

  renderLink(url) {
    const onClick = event => {
      event.preventDefault();

      if (history.state !== url) {
        history.replaceState(null, url);
      }
    };

    return <div><a href="#" onClick={onClick}>{url}</a></div>;
  }

  render() {
    return (
      <div className="chat-triggers">
        {this.renderLink('/chat/begin/simple')}
        {this.renderLink('/chat/begin/conversation')}
        {this.renderLink('/chat/begin/form')}
        {this.renderLink('/chat/active')}
      </div>
    );
  }
}

export class ChatTriggers extends React.Component {

  static propTypes = {
    isVisible: PropTypes.bool
  };

  render() {
    const { isVisible } = this.props;
    const style = {
      margin: '14px'
    };

    return (
      <Frame ref="frame"
             frameStyles={style}
             isVisible={isVisible}
             positionMode="bottom.left">

        <ChatTriggersBody {...this.props} onResize={() => this.refs.frame && this.refs.frame.autoFrameDimensions()} />
      </Frame>
    );
  }
}
