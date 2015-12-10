import React, { PropTypes } from 'react';
import { AgentMessage } from './Message/AgentMessage';
import { UserMessage } from './Message/UserMessage';
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
      const pharses = window.DESKPRO_LANG || {};
      const translatedText = pharses[`user.chat.${phraseId}`];

      return (
        <InlineEvent {...this.props}>
          {translatedText.replace('{{name}}', content.name)}
        </InlineEvent>
      );
    }

    return isAgent ? <AgentMessage {...this.props} /> : <UserMessage {...this.props} />;
  }
}
