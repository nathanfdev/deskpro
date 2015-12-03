import React from 'react';
import { Message } from './Message';
import { MessageAvatar } from './MessageAvatar';
import { MessageContent } from './MessageContent';
import { MessageFooter } from './MessageFooter';

export class UserMessage extends React.Component {

  render() {
    return (
      <Message type="user">
        <MessageAvatar {...this.props} />
        <MessageContent {...this.props} />
        <MessageFooter {...this.props} />
      </Message>
    );
  }
}
