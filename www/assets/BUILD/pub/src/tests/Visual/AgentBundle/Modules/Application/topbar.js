import React from 'react';
import { storiesOf, action } from '@kadira/storybook';
import AgentTopBar from 'DeskPRO/Bundle/AgentBundle/Modules/TopBar/AgentTopBar';

const agents = [
  {
    name:       'Julien Ducro',
    department: 'Support'
  }
];

storiesOf('App: top bar', module)
  .add(
    'Top bar',
    () => <AgentTopBar agents={agents} onSearch={action('SearchInput')} />
  )
;
