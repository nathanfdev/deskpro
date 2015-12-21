import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { InlineEvent } from './Event/InlineEvent';
import { Message } from './Message/Message';
import { MessageAvatar } from './Message/MessageAvatar';
import { MessageBody } from './Message/MessageBody';
import { MessageAttachment } from './Message/Attachment/MessageAttachment';
import { MessageContent } from './Message/MessageContent';
import { MessageFooter } from './Message/MessageFooter';
import Immutable from 'immutable';
import { phraseTranslationsSelector } from '../../../../Selectors/chat';

@connect(state => ({
  phraseTranslations: phraseTranslationsSelector(state)
}))
export class MessageFactoryContainer extends React.Component {

  static propTypes = {
    phraseTranslations: PropTypes.object,
    message: PropTypes.object
  };

  renderEvent() {
    const { message, phraseTranslations } = this.props;

    const content = JSON.parse(message.get('content'));
    const phraseId = content.phrase_id;
    const translatedText = String(phraseTranslations.get(`user.chat.${phraseId}`));

    // Should display disconnected block
    if (phraseId === 'message_agent-timeout') {
      return null;
    }

    return (
      <InlineEvent {...this.props}>
        {translatedText.replace('{{name}}', content.name)}
      </InlineEvent>
    );
  }

  renderMessage() {
    const { message } = this.props;
    const metadata = message.get('metadata') || Immutable.fromJS({});

    return (
      <Message type={message.get('author_type')}>
        <MessageAvatar url={message.get('author_avatar')} />
        <MessageBody>
          {metadata.get('type') === 'file'
            ? <MessageAttachment {...this.props} />
            : <MessageContent {...this.props} />
          }
        </MessageBody>
        <MessageFooter {...this.props} />
      </Message>
    );
  }

  render() {
    return this.props.message.get('is_sys') ? this.renderEvent() : this.renderMessage();
  }
}
