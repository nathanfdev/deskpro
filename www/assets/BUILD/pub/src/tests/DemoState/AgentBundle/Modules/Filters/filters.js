import { fromJS } from 'immutable';

export const filterSets = [
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
  },
  {
    id:            2,
    title:         'Filters',
    display_order: 0,
    filters:       [
      6,
      7,
    ],
    share_mode:    'global',
    shared_teams:  [],
    shared_agents: []
  }
];

const filtersArray = [
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
    ticket_filter_set: 1,
    filterable:        true,
  },
  {
    id:                6,
    title:             'Demo',
    query:             "ticket.status = 'awaiting_agent'",
    display_order:     10,
    ticket_filter_set: 1
  },
  {
    id:                7,
    title:             'Pricing',
    query:             "ticket.status = 'awaiting_agent'",
    display_order:     20,
    ticket_filter_set: 1,
  },
];
export const filters = fromJS(filtersArray);

export const stars = [
  {
    id:    1,
    title: 'Bug',
    color: '#4696dc'
  },
  {
    id:    2,
    title: 'Green',
    color: '#54c66a'
  },
  {
    id:    3,
    title: 'Yellow',
    color: '#f9d6a4'
  }
];

export const labels = [
  'Android Mobile App',
  'Bogus',
  'Bug fixed',
  'Cannot Reproduce',
  'Case Study',
  'Churn',
  'Beta tester',
  'Click Jacking',
  'cloud-ips',
  'Consultation Session',
  'Custom fields',
  'Capterra',
  'Did it Work',
  'dog house',
  'Converted',
  'Custom work',
  'label',
  'Demo',
  'Integrations',
  'HTML',
  'Cloud',
  'Email',
  'Editor',
  'Enumeration',
  'Games',
  'iPad',
  'Fixed',
  'Mobile'
];
