import React, { PropTypes } from 'react';
import { AgentMessage } from './Message/AgentMessage';
import { UserMessage } from './Message/UserMessage';
import { StartChatEvent } from './Event/Inline/StartChatEvent';
import { JoinedEvent } from './Event/Inline/JoinedEvent';
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

  static renderMessage(message, index) {
    if (message.get('is_sys')) {
      const sysContent = JSON.parse(message.get('content'));
      switch (sysContent.phrase_id) {
        case 'message_started':
          return <StartChatEvent key={index} message={message} />;
        case 'message_assigned':
          return <JoinedEvent key={index} message={message} />;
        default:
          return null;
      }
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
            {messages.map((message, index) => MessagesList.renderMessage(message, index))}
          </div>
        </ScrollArea>
      </div>
    );
  }
}
