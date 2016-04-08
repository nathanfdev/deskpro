import React, { PropTypes } from 'react';
import { Message } from '../Message/Message';
import { MessageAvatar } from '../Message/MessageAvatar';
import { AvatarResolver } from 'DeskPRO/Component/Avatar';

export class TypingEvent extends React.Component {

  static propTypes = {
    agentName:   PropTypes.string,
    agentAvatar: PropTypes.object
  };

  render() {
    const { agentName, agentAvatar } = this.props;

    return (
      <Message type="agent" typing>
        <AvatarResolver avatar={agentAvatar} size={20}>
          <MessageAvatar />
        </AvatarResolver>
        <div className="dpdesignportal-message-content">
          <span className="dpdesignportal-user-typing">{agentName} is typing a message
            <span className="dot1">.</span>
            <span className="dot2">.</span>
            <span className="dot3">.</span>
          </span>
        </div>
      </Message>
    );
  }
}
