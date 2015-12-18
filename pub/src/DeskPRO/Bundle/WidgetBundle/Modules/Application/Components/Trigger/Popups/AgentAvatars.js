import React, { PropTypes } from 'react';
import classNames from 'classnames';
import SampleAvatar from '../../../../../Resources/img/sample-avatar.jpg';

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
        <ul className={classNames({'multiple': !primaryAgent})}>
          {displayAgents.map((agent, index) =>
              <li key={index}>
                <div className="dpdesignportal-chat-header-avatar" style={{backgroundImage: `url(${SampleAvatar})`}} />
              </li>
          )}
        </ul>
        {primaryAgent && <hr/>}
      </div>
    );
  }
}
