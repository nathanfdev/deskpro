import React, { PropTypes } from 'react';
import classNames from 'classnames';
import SampleAvatar from '../../../../../Resources/img/sample-avatar.jpg';

export class AgentAvatars extends React.Component {

  static propTypes = {
    onlineAgents: PropTypes.object,
    multiple: PropTypes.bool
  };

  render() {
    const { onlineAgents, multiple } = this.props;
    const displayAgents = onlineAgents.slice(0, multiple ? 3 : 1);

    return (
      <div className="avatar-container">
        <ul className={classNames({'multiple': multiple})}>
          {displayAgents.map((agent, index) =>
              <li key={index}>
                <div className="dpdesignportal-chat-header-avatar" style={{backgroundImage: `url(${SampleAvatar})`}} />
              </li>
          )}
        </ul>
        {!multiple && <hr/>}
      </div>
    );
  }
}
