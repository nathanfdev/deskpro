import PropTypes from 'prop-types';
import React from 'react';
import { PersonAvatar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar/index';
import classNames from 'classnames';
import Immutable from 'immutable';

class Avatar extends React.Component {

  static propTypes = {
    online:           PropTypes.bool,
    withOnlineStatus: PropTypes.bool,
    size:             PropTypes.number,
    person:           PropTypes.object
  };

  render() {
    const { size, person, online, withOnlineStatus } = this.props;
    const styles = {
      width:  size,
      height: size
    };

    const agentData = person && person.get('agent_data') || Immutable.fromJS({});
    const forwarding = agentData.get('forwarding_number')
      && agentData.get('can_use_forwarding')
      && agentData.get('agent_can_use_forwarding')
      && ((online && !agentData.get('forwarding_logged_out')) || !online);

    return (
      <div className="voice-avatar-container">
        {withOnlineStatus && <i className={classNames('online-status', { online, forwarding })} />}
        <div className={classNames('avatar', { online, 'with-online-status': withOnlineStatus })} style={styles}>
          <i className="fas fa-user" />
          {person && <PersonAvatar person={person} size={size} />}
        </div>
      </div>
    );
  }
}

export default Avatar;
