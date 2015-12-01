import React, { PropTypes } from 'react';
import Frame from 'Ampliflux/common/components/Frame';

export default class ChatTriggersBody extends React.Component {

  static propTypes = {
    onOpen: PropTypes.func,
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
      this.props.onOpen(url);
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
    onClick: PropTypes.func,
    isVisible: PropTypes.bool
  };

  render() {
    const { isVisible } = this.props;
    const style = {
      margin: '14px'
    };

    return (
      <Frame ref="frame"
             style={style}
             isVisible={isVisible}
             positionMode="bottom.left">

        <ChatTriggersBody {...this.props} onResize={() => this.refs.frame && this.refs.frame.autoFrameDimensions()} />
      </Frame>
    );
  }
}
