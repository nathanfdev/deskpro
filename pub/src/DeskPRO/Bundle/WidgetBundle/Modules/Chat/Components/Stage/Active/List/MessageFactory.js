import React, { PropTypes } from 'react';
import { AgentMessage } from './Message/AgentMessage';
import { UserMessage } from './Message/UserMessage';
import { StartChatEvent } from './Event/Inline/StartChatEvent';
import { JoinedEvent } from './Event/Inline/JoinedEvent';

export class MessageFactory extends React.Component {

  static propTypes = {
    message: PropTypes.object
  };

  render() {
    const { message } = this.props;
    const isAgent = message.get('author_type') === 'agent';

    if (message.get('is_sys')) {
      const content = JSON.parse(message.get('content'));
      const phraseId = content.phrase_id;

      const props = this.props;
      const eventProps = {
        ...props,

        content,
        translatedText: window.DESKPRO_LANG[`user.chat.${phraseId}`]
      };

      switch (phraseId) {
        case 'message_started':
          return <StartChatEvent {...eventProps} />;
        case 'message_assigned':
          return <JoinedEvent {...eventProps} />;
        case 'message_ended-by-user':
          // todo
          return null;
        default:
          // todo tmp, for dev
          alert(`unknown phrase id "${phraseId}"`);

          return null;
      }
    }

    return isAgent ? <AgentMessage {...this.props} /> : <UserMessage {...this.props} />;
  }
}
