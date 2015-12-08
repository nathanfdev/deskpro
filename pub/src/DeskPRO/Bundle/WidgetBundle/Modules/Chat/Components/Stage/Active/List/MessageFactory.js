import React, { PropTypes } from 'react';
import { AgentMessage } from './Message/AgentMessage';
import { UserMessage } from './Message/UserMessage';
import { MessageAvatar } from './Message/MessageAvatar';
import { InlineEvent } from './Event/InlineEvent';

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
      const translatedText = window.DESKPRO_LANG[`user.chat.${phraseId}`];

      switch (phraseId) {
        case 'message_ended-by-user':
        case 'message_started':
          return (
            <InlineEvent {...this.props}>
              <MessageAvatar /> {translatedText}
            </InlineEvent>
          );

        case 'message_assigned':
          return (
            <InlineEvent {...this.props}>
              {translatedText.replace('{{name}}', content.name)}
            </InlineEvent>
          );

        default:
          // todo tmp, for dev
          alert(`unknown phrase id "${phraseId}"`);

          return null;
      }
    }

    return isAgent ? <AgentMessage {...this.props} /> : <UserMessage {...this.props} />;
  }
}
