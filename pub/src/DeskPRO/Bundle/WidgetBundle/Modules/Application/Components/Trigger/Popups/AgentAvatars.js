import React, { PropTypes } from 'react';
import classNames from 'classnames';

export class AgentAvatars extends React.Component {

  static propTypes = {
    onlineAgents: PropTypes.object,
    primaryAgent: PropTypes.object
  };

  static renderAvatar(agent, index) {
    const avatar = agent.get('avatar');
    const style = {};
    if (avatar) {
      style.backgroundImage = `url(${avatar})`;
    }

    return (
      <li key={index}>
        <div className="dpdesignportal-chat-header-avatar" style={style}>
          <i className="fa fa-user"></i>
        </div>
      </li>
    );
  }

  render() {
    const { onlineAgents, primaryAgent } = this.props;
    const displayAgents = primaryAgent ? [primaryAgent] : onlineAgents.slice(0, 3);

    return (
      <div className="avatar-container">
        <ul className={classNames({'multiple': !primaryAgent})}>
          {displayAgents.map((agent, index) => AgentAvatars.renderAvatar(agent, index))}
        </ul>
        {primaryAgent && <hr/>}
      </div>
    );
  }
}
