import React, { PropTypes } from 'react';
import ScrollArea from 'react-scrollbar';
import { MessageFactory } from './MessageFactory';

export class MessagesList extends React.Component {

  static propTypes = {
    messages: PropTypes.object,
    isEnded: PropTypes.bool
  };

  componentDidMount() {
    this.scrollBottom();
  }

  componentDidUpdate() {
    this.scrollBottom();
  }

  scrollBottom() {
    setTimeout(() => this.refs.scrollArea.scrollBottom(), 0);
  }

  render() {
    const { messages } = this.props;

    return (
      <div className="dpdesignportal-content">
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
