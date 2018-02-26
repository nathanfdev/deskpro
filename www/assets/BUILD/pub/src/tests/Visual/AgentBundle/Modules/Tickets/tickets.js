import React from 'react';
import Immutable from 'immutable';
import moment from 'moment';
import { storiesOf, action } from '@storybook/react'; // eslint-disable-line import/no-extraneous-dependencies
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

const followUps = Immutable.fromJS(
  [
    {
      id:      1,
      person:  1,
      actions: [
        { type: 'agent', options: { agent: '2' } },
        { type: 'status', options: { status: 'awaiting_agent' } }
      ],
      cancel_if_user_reply: false,
      status:               'pending',
      date_created:         moment().subtract(1, 'days').format(),
      date_to_run:          moment().add(6, 'hours').format(),
      date_did_run:         null
    },
    {
      id:      2,
      person:  3,
      actions: [
        { type: 'hold', options: { is_hold: 1 } },
      ],
      cancel_if_user_reply: false,
      status:               'done',
      date_created:         moment().subtract(1, 'days').format(),
      date_to_run:          moment().subtract(2, 'hours').format(),
      date_did_run:         moment().subtract(2, 'hours').format()
    },
    {
      id:      3,
      person:  1,
      actions: [
        { type: 'reply', options: { reply: 'Dear John, please ' } },
      ],
      cancel_if_user_reply: false,
      status:               'cancelled',
      date_created:         moment().subtract(1, 'days').format(),
      date_to_run:          moment().add(20, 'minutes').format(),
      date_did_run:         null
    }
  ]
);

const macros = Immutable.fromJS([
  {
    summary: [
      'Assign agent to Bernice Nikolaus'
    ],
    id:         1,
    person:     1,
    department: null,
    title:      'Set Agent',
    is_enabled: true,
    is_global:  false,
    actions:    [
      {
        type:    'agent',
        options: {
          agent: '8'
        }
      }
    ]
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
      followUps={followUps}
      macros={macros}
      deleteFollowUp={action('delete Follow Up')}
      saveFollowUp={action('save Follow Up')}
    />
  )
;
