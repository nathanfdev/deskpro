import React from 'react';
import Accordion from 'DeskPRO/Component/Semantic/Accordion/Accordion';
import QueuesToggleContainer from './QueuesToggleContainer';
import VolumeContainer from './VolumeContainer';
import Voicemail from './Voicemail';

class Settings extends React.Component {

  render() {
    const props = {
      panels: [
        {
          title:   'Queues',
          icon:    'fa fa-tasks',
          content: <QueuesToggleContainer />
        },
        {
          title:   'Ringing volume',
          icon:    'fa fa-volume-up',
          content: <VolumeContainer />
        },
        {
          title:   'Voicemail',
          icon:    'fa fa-play-circle',
          content: <Voicemail />
        }
      ]
    };

    return <Accordion {...props} />;
  }
}

export default Settings;
