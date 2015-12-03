import React, { PropTypes } from 'react';
import { AgentMessage } from './Message/AgentMessage';
import { UserMessage } from './Message/UserMessage';
import { JoinedEvent } from './Event/JoinedEvent';
import ScrollArea from 'react-scrollbar';

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

  renderMessage(message, index) {
    if (message.get('is_sys')) {
      return <JoinedEvent key={index} message={message} />;
    }
    if (message.get('author')) {
      return <AgentMessage key={index} message={message} />;
    }

    return <UserMessage key={index} message={message} />;
  }

  render() {
    const { messages } = this.props;

    return (
      <div className="dpdesignportal-content">
        <ScrollArea ref="scrollArea" vertical>
          <div className="bottom-aligner"/>
          <div>
            {messages.map((message, index) => this.renderMessage(message, index))}
          </div>
        </ScrollArea>
      </div>
    );
  }
}
