import React from 'react';
import Immutable from 'immutable';
import { storiesOf } from '@kadira/storybook'; // eslint-disable-line import/no-extraneous-dependencies
import { ArchiveFiles } from 'DeskPRO/Bundle/AgentBundle/Modules/Tickets/Components/Archive/ArchiveFiles';
import { FollowUp } from 'DeskPRO/Bundle/AgentBundle/Modules/Tickets/Components/FollowUp/FollowUp';
import { list } from 'DemoState/AgentBundle/Modules/Tickets/tickets';
import { agentTeams } from 'DemoState/AgentBundle/Modules/Snippets/snippets';
import { css } from '../../../decorators';

const agents = Immutable.fromJS([
  {
    id:   1,
    name: 'Agent 1'
  },
  {
    id:   2,
    name: 'Agent 2'
  },
  {
    id:   3,
    name: 'Agent 3'
  },
  {
    id:   4,
    name: 'Agent 4'
  },
  {
    id:   5,
    name: 'Agent 5'
  },
  {
    id:   6,
    name: 'Agent 6'
  },
  {
    id:   7,
    name: 'Agent 7'
  },
  {
    id:   8,
    name: 'Agent 8'
  }
]);

storiesOf('Agent: Tickets', module)
  .addDecorator(story => css(story()))
  .add(
    'Archive Files',
    () => <ArchiveFiles list={list} />
  )
  .add(
    'Follow Up',
    () => <FollowUp
      agents={agents}
      agentTeams={agentTeams}
    />
  )
;
