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

  static renderMessage(message, key) {
    const props = {key, message};
    const isAgent = message.get('author_type') === 'agent';

    if (message.get('is_sys')) {
      const sysContent = JSON.parse(message.get('content'));
      switch (sysContent.phrase_id) {
        case 'message_started':
          return <StartChatEvent {...props} />;
        case 'message_assigned':
          return <JoinedEvent {...props} />;
        default:
          return null;
      }
    }

    return isAgent ? <AgentMessage {...props} /> : <UserMessage {...props} />;
  }

  render() {
    const { messages } = this.props;

    return (
      <div className="dpdesignportal-content">
        <ScrollArea ref="scrollArea" vertical>
          <div className="bottom-aligner"/>
          <div>
            {messages.map((message, key) => MessagesList.renderMessage(message, key))}
          </div>
        </ScrollArea>
      </div>
    );
  }
}
