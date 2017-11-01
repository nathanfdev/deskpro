import PropTypes from 'prop-types';
import React from 'react';
import { AvatarResolver } from 'DeskPRO/Component/Avatar';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';
import { Message } from '../Message/Message';
import { MessageAvatar } from '../Message/MessageAvatar';

export class TypingEvent extends React.Component {

  static propTypes = {
    agentName:   PropTypes.string,
    agentAvatar: PropTypes.object
  };

  render() {
    const { agentName, agentAvatar } = this.props;

    return (
      <Message type="agent" typing>
        <AvatarResolver avatar={agentAvatar} size={40}>
          <MessageAvatar />
        </AvatarResolver>
        <div className="dpdesignportal-message-content">
          <span className="dpdesignportal-user-typing">
            {portalPhrases.get('portal.chat.agent_typing_message', { '{agentName}': agentName })}
            <span className="dot1">.</span>
            <span className="dot2">.</span>
            <span className="dot3">.</span>
          </span>
        </div>
      </Message>
    );
  }
}
export default TypingEvent;
