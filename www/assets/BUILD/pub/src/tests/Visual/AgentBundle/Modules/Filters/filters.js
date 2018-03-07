import React from 'react';
import { storiesOf } from '@storybook/react';
import { fromJS } from 'immutable';
import { css } from 'Visual/decorators';
import AgentFilters from 'DeskPRO/Bundle/AgentBundle/Modules/Filters/Components/Filters';

const filterSets = [
  {
    id:            1,
    title:         'Inbox',
    display_order: 0,
    filters:       [
      1,
      2,
      3,
      4,
      5
    ],
    share_mode:    'global',
    shared_teams:  [],
    shared_agents: []
  }
];

const filters = [
  {
    id:                1,
    title:             'Assigned To Me',
    query:             "ticket.status = 'awaiting_agent' AND ticket.agent = $me",
    display_order:     10,
    ticket_filter_set: 1
  },
  {
    id:                2,
    title:             'Tickets I Follow',
    query:             "ticket.status = 'awaiting_agent' AND ticket.followers HAS $me",
    display_order:     20,
    ticket_filter_set: 1
  },
  {
    id:                3,
    title:             'Assigned To Team',
    query:             "ticket.status = 'awaiting_agent' AND ticket.agent_team IN $my_teams",
    display_order:     30,
    ticket_filter_set: 1
  },
  {
    id:                4,
    title:             'Unassigned',
    query:             "ticket.status = 'awaiting_agent' AND ticket.agent IS EMPTY",
    display_order:     40,
    ticket_filter_set: 1
  },
  {
    id:                5,
    title:             'All Awaiting Agent',
    query:             "ticket.status = 'awaiting_agent'",
    display_order:     50,
    ticket_filter_set: 1
  }
];

storiesOf('Agent: Filters', module)
  .addDecorator(story => css(story()))
  .add(
    'Filters',
    () =>
      <div id="react_dp_agent_filters">
        <AgentFilters
          filterSets={filterSets}
          filters={fromJS(filters)}
        />
      </div>
  )
;
