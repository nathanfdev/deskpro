import PropTypes from 'prop-types';
import React from 'react';
import { AvatarResolver } from 'DeskPRO/Component/Avatar';
import { AgentAvatar } from './AgentAvatar';
import classNames from 'classnames';

export class AgentAvatars extends React.Component {

  static propTypes = {
    onlineAgents: PropTypes.object,
    primaryAgent: PropTypes.object
  };

  render() {
    const { onlineAgents, primaryAgent } = this.props;
    const displayAgents = primaryAgent ? [primaryAgent] : onlineAgents.slice(0, 3);

    return (
      <div className="avatar-container">
        <ul className={classNames({ multiple: !primaryAgent })}>
          {displayAgents.map((agent, index) =>
            <li key={index}>
              <AvatarResolver avatar={agent.get('avatar')} size={100}>
                <AgentAvatar />
              </AvatarResolver>
            </li>
          )}
        </ul>
      </div>
    );
  }
}
