import React from 'react';
import { storiesOf } from '@kadira/storybook'; // eslint-disable-line import/no-extraneous-dependencies
import ArchiveMenu from 'DeskPRO/Bundle/AgentBundle/Modules/Tickets/Components/Archive/ArchiveMenu';
import { list } from 'DemoState/AgentBundle/Modules/Tickets/tickets';

storiesOf('Agent: Tickets', module)
  .add(
    'Archive Menu',
    () => <ArchiveMenu list={list} />
  )
;
