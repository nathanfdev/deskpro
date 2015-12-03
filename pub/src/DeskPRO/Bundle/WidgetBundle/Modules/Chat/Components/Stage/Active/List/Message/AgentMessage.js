import React from 'react';
import { Message } from './Message';
import { MessageAvatar } from './MessageAvatar';
import { MessageContent } from './MessageContent';
import { MessageFooter } from './MessageFooter';

export class AgentMessage extends React.Component {

  render() {
    return (
      <Message type="agent">
        <MessageAvatar {...this.props} />
        <MessageContent {...this.props} />
        <MessageFooter {...this.props} />
      </Message>
    );
  }
}
