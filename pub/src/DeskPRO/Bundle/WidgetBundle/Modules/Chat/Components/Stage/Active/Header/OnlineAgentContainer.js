import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { agentNameSelector, agentAvatarSelector } from '../../../../Selectors/chat';

@connect(state => ({
  agentName: agentNameSelector(state),
  agentAvatar: agentAvatarSelector(state)
}))
export class OnlineAgentContainer extends React.Component {

  static propTypes = {
    agentName: PropTypes.string,
    agentAvatar: PropTypes.string
  };

  render() {
    const { agentName } = this.props;

    return (
      <div className="dpdesignportal-chat-header">
        <div className="dpdesignportal-chat-header-avatar-container multiple">
          <ul>
            <li><div className="dpdesignportal-chat-header-avatar"></div></li>
          </ul>
        </div>
        <hr/>
        <h1>You are chatting with <span>{agentName}</span></h1>
        <h2>DeskPRO, Customer Support Representatives</h2>
      </div>
    );
  }
}
