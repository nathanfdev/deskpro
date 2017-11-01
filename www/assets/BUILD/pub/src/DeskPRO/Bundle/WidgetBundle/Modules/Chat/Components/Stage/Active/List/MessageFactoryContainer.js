import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import Immutable from 'immutable';
import { AvatarResolver } from 'DeskPRO/Component/Avatar';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';
import { InlineEvent } from './Event/InlineEvent';
import { Message } from './Message/Message';
import { MessageAvatar } from './Message/MessageAvatar';
import { MessageBody } from './Message/MessageBody';
import { MessageAttachment } from './Message/Attachment/MessageAttachment';
import { MessageContent } from './Message/MessageContent';
import { MessageFooter } from './Message/MessageFooter';
import { authorNameSelector } from '../../../../Selectors/chat';
import { peopleSelector } from '../../../../../Application/Selectors/peopleSelectors';
import { primaryColorSelector } from '../../../../../Application/Selectors/dpWindow';

@connect(state => ({
  chatAuthorName: authorNameSelector(state),
  people:         peopleSelector(state),
  primaryColor:   primaryColorSelector(state)
}))
export class MessageFactoryContainer extends React.Component {

  static propTypes = {
    chatAuthorName: PropTypes.string,
    people:         PropTypes.object,
    message:        PropTypes.object,
    primaryColor:   PropTypes.string
  };

  getAuthor() {
    const { message, people } = this.props;
    const authorId = message.get('author');

    return (authorId && people.get(authorId)) || Immutable.fromJS({ id: authorId, display_name: `ID ${authorId}` });
  }

  getMetadata() {
    return this.props.message.get('metadata') || Immutable.fromJS({});
  }

  getAuthorName() {
    const { message, chatAuthorName } = this.props;
    if (message.get('is_sys')) {
      return '*';
    }

    const author = this.getAuthor();
    if (author && author.get('display_name')) {
      return author.get('display_name');
    }

    return message.get('is_user') ? chatAuthorName : 'Agent';
  }

  renderEvent() {
    const { message } = this.props;

    const content = JSON.parse(message.get('content'));
    const phraseId = content.phrase_id;
    const translatedText = String(portalPhrases.get(`user.chat.${phraseId}`));

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
    const { message, primaryColor } = this.props;
    const metadata = this.getMetadata();
    const author = this.getAuthor();
    const authorName = this.getAuthorName();

    const props = this.props;
    const messageProps = { ...props, author, authorName };

    return (
      <Message isUser={message.get('is_user')}>
        <AvatarResolver avatar={author.get('avatar')} size={40}>
          <MessageAvatar primaryColor={primaryColor} />
        </AvatarResolver>
        <MessageBody>
          {metadata.get('type') === 'file'
            ? <MessageAttachment {...messageProps} />
            : <MessageContent {...messageProps} />
          }
        </MessageBody>
        <MessageFooter {...messageProps} />
      </Message>
    );
  }

  render() {
    const { message } = this.props;
    const author = this.getAuthor();

    // Message has author but his info doesn't loaded yet
    // Don't render message until it will be loaded
    if (message.get('author') && !author.get('id')) {
      return null;
    }

    return message.get('is_sys') ? this.renderEvent() : this.renderMessage();
  }
}
export default MessageFactoryContainer;
