import React from 'react';
import Accordion from 'DeskPRO/Component/Semantic/Accordion/Accordion';
import QueuesToggleContainer from './QueuesToggleContainer';
import Volume from './Volume';
import CallForward from './CallForward';

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
          content: <Volume />
        },
        {
          title:   'Call forwarding',
          icon:    'fa fa-mail-forward',
          content: <CallForward />
        }
      ]
    };

    return <Accordion {...props} />;
  }
}

export default Settings;
