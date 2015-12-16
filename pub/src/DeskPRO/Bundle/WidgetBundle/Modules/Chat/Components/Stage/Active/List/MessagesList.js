import React, { PropTypes } from 'react';
import ScrollArea from 'react-scrollbar';
import { MessageFactory } from './MessageFactory';

export class MessagesList extends React.Component {

  static propTypes = {
    height: PropTypes.number,
    messages: PropTypes.object,
    isEnded: PropTypes.bool
  };

  componentDidUpdate() {
    this.scrollBottom();
  }

  scrollBottom() {
    if (this.refs.scrollArea) {
      setTimeout(() => this.refs.scrollArea.scrollBottom(), 0);
    }
  }

  render() {
    const { messages, height } = this.props;

    return (
      <div className="dpdesignportal-content" style={{height: `${height}px`}}>
        <ScrollArea ref="scrollArea" vertical>
          <div className="bottom-aligner"/>
          <div>
            {messages.map((message, key) => <MessageFactory key={key} message={message} />)}
          </div>
        </ScrollArea>
      </div>
    );
  }
}
