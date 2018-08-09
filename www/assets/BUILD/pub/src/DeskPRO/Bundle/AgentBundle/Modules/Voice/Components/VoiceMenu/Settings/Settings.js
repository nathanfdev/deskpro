import PropTypes from 'prop-types';
import React from 'react';
import Accordion from 'DeskPRO/Component/Semantic/Accordion/Accordion';
import QueuesToggleContainer from './QueuesToggleContainer';
import VolumeContainer from './VolumeContainer';
import Voicemail from './Voicemail';
import OnlineAgentsContainer from './OnlineAgentsContainer';
import CallForwardContainer from './CallForwardContainer';

class Settings extends React.Component {

  static propTypes = {
    me: PropTypes.object
  };

  render() {
    const { me } = this.props;
    const canUseForwarding = me.getIn(['agent_data', 'can_use_forwarding']);
    const props = {
      panels: []
    };

    props.panels.push({
      title:   'Queues',
      icon:    'fa fa-tasks',
      content: <QueuesToggleContainer />
    });
    props.panels.push({
      title:   'Ringing volume',
      icon:    'fa fa-volume-up',
      content: <VolumeContainer />
    });
    props.panels.push({
      title:   'Voicemail',
      icon:    'fa fa-play-circle',
      content: <Voicemail />
    });

    if (canUseForwarding) {
      props.panels.push({
        title:   'Call forwarding',
        icon:    'fas fa-share',
        content: <CallForwardContainer />
      });
    }

    return (
      <div>
        <Accordion {...props} />
        <OnlineAgentsContainer />
      </div>
    );
  }
}

export default Settings;
