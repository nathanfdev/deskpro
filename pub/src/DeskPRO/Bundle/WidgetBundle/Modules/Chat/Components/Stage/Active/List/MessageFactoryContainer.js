import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { InlineEvent } from './Event/InlineEvent';
import { Message } from './Message/Message';
import { MessageAvatar } from './Message/MessageAvatar';
import { MessageBody } from './Message/MessageBody';
import { MessageAttachment } from './Message/Attachment/MessageAttachment';
import { MessageContent } from './Message/MessageContent';
import { MessageFooter } from './Message/MessageFooter';
import { AvatarResolver } from '../../../../../Application/Components/AvatarResolver';
import { phraseTranslationsSelector, authorNameSelector } from '../../../../Selectors/chat';
import { peopleSelector } from '../../../../../Application/RecordStores/Selectors/peopleSelectors';
import Immutable from 'immutable';

@connect(state => ({
  authorName: authorNameSelector(state),
  people: peopleSelector(state),
  phraseTranslations: phraseTranslationsSelector(state)
}))
export class MessageFactoryContainer extends React.Component {

  static propTypes = {
    authorName: PropTypes.string,
    people: PropTypes.object,
    phraseTranslations: PropTypes.object,
    message: PropTypes.object
  };

  getAuthor() {
    const { message, people } = this.props;
    const authorId = message.get('author');

    return authorId && people.get(authorId) || Immutable.fromJS({});
  }

  getMetadata() {
    return this.props.message.get('metadata') || Immutable.fromJS({});
  }

  getAuthorType() {
    const author = this.getAuthor();
    const metadata = this.getMetadata();

    let authorType = author.get('is_agent') ? 'agent' : 'user';
    if (metadata.get('is_user_message')) {
      authorType = 'user';
    }

    return authorType;
  }

  getAuthorName() {
    const { message, authorName } = this.props;
    if (message.get('is_sys')) {
      return '*';
    }

    const author = this.getAuthor();
    if (author && author.get('display_name')) {
      return author.get('display_name');
    }

    const authorType = this.getAuthorType();
    return authorType === 'user' ? authorName : 'Agent';
  }

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
    const metadata = this.getMetadata();
    const authorType = this.getAuthorType();
    const author = this.getAuthor();
    const authorName = this.getAuthorName();

    const props = this.props;
    const messageProps = { ...props, authorType, author, authorName };

    return (
      <Message type={authorType}>
        <AvatarResolver avatar={author.get('avatar')} size={20}>
          <MessageAvatar />
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
