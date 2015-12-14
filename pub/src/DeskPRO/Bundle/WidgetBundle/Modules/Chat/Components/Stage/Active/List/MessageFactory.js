import React, { PropTypes } from 'react';
import { InlineEvent } from './Event/InlineEvent';
import { Message } from './Message/Message';
import { MessageAvatar } from './Message/MessageAvatar';
import { MessageContent } from './Message/MessageContent';
import { MessageFooter } from './Message/MessageFooter';

export class MessageFactory extends React.Component {

  static propTypes = {
    message: PropTypes.object
  };

  render() {
    const { message } = this.props;

    if (message.get('is_sys')) {
      const content = JSON.parse(message.get('content'));
      const phraseId = content.phrase_id;
      const pharses = window.DESKPRO_LANG || {};
      const translatedText = String(pharses[`user.chat.${phraseId}`]);

      return (
        <InlineEvent {...this.props}>
          {translatedText.replace('{{name}}', content.name)}
        </InlineEvent>
      );
    }

    return (
      <Message type={message.get('author_type')}>
        <MessageAvatar {...this.props} />
        <MessageContent {...this.props} />
        <MessageFooter {...this.props} />
      </Message>
    );
  }
}
