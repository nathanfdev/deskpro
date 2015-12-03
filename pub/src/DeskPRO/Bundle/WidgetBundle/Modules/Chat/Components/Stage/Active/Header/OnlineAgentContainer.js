import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { agentNameSelector, agentAvatarSelector, departmentNameSelector } from '../../../../Selectors/chat';

@connect(state => ({
  agentName: agentNameSelector(state),
  agentAvatar: agentAvatarSelector(state),
  departmentName: departmentNameSelector(state)
}))
export class OnlineAgentContainer extends React.Component {

  static propTypes = {
    agentName: PropTypes.string,
    agentAvatar: PropTypes.string,
    departmentName: PropTypes.string
  };

  render() {
    const { agentAvatar, agentName, departmentName } = this.props;

    return (
      <div className="dpdesignportal-chat-header">
        <div className="dpdesignportal-chat-header-avatar-container multiple">
          <ul>
            {agentAvatar &&
              <li>
                <div className="dpdesignportal-chat-header-avatar" style={{backgroundImage: `url(${agentAvatar})`}} />
              </li>
            }
          </ul>
        </div>
        <hr/>
        <h1>You are chatting with <span>{agentName}</span></h1>
        <h2>{departmentName}</h2>
      </div>
    );
  }
}
