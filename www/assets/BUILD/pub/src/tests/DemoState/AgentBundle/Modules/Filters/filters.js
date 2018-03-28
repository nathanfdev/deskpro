import { fromJS } from 'immutable';

export const filterSetsArray = [
  {
    id:            1,
    title:         'Inbox',
    display_order: 0,
    filters:       [
      101,
      103,
      3,
      104,
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
export const filterSets = fromJS(filterSetsArray);

const filtersArray = [
  {
    id:                101,
    title:             'Assigned To Me',
    query:             "ticket.status = 'awaiting_agent' AND ticket.agent = $me",
    display_order:     10,
    ticket_filter_set: 1
  },
  {
    id:                103,
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
    id:                104,
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
    count:             301,
    value:             null,
    type:              null,
    grouped_by:        'ticket.agent',
    nested:            [
      {
        count:      20,
        id:         1,
        value:      null,
        type:       'ticket.agent',
        title:      'Julien Ducro',
        grouped_by: null,
        nested:     []
      },
      {
        count:      32,
        id:         2,
        value:      null,
        type:       'ticket.agent',
        title:      'Hillard Bins',
        grouped_by: null,
        nested:     []
      },
      {
        count:      18,
        id:         3,
        value:      null,
        type:       'ticket.agent',
        title:      'Colin Greenfelder',
        grouped_by: null,
        nested:     []
      },
      {
        count:      27,
        id:         4,
        value:      null,
        type:       'ticket.agent',
        title:      'Conor Emmerich',
        grouped_by: null,
        nested:     []
      },
      {
        count:      34,
        id:         5,
        value:      null,
        type:       'ticket.agent',
        title:      'Maurice Witting',
        grouped_by: null,
        nested:     []
      },
      {
        count:      24,
        id:         6,
        value:      null,
        type:       'ticket.agent',
        title:      'Nickolas Pfeffer',
        grouped_by: null,
        nested:     []
      },
      {
        count:      24,
        id:         7,
        value:      null,
        type:       'ticket.agent',
        title:      'Corine Dietrich',
        grouped_by: null,
        nested:     []
      },
      {
        count:      23,
        id:         8,
        value:      null,
        type:       'ticket.agent',
        title:      'Annette Predovic',
        grouped_by: null,
        nested:     []
      },
      {
        count:      21,
        id:         9,
        value:      null,
        type:       'ticket.agent',
        title:      'Clark Altenwerth',
        grouped_by: null,
        nested:     []
      },
      {
        count:      17,
        id:         10,
        value:      null,
        type:       'ticket.agent',
        title:      "Jordon D'Amore",
        grouped_by: null,
        nested:     []
      },
      {
        count:      22,
        id:         11,
        value:      null,
        type:       'ticket.agent',
        title:      'Camryn Kerluke',
        grouped_by: null,
        nested:     []
      },
      {
        count:      12,
        id:         512,
        value:      null,
        type:       'ticket.agent',
        title:      'Corporate Content',
        grouped_by: null,
        nested:     []
      },
      null
    ]
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

const starsArray = [
  {
    id:    1,
    name:  'Blue',
    color: 'blue',
    hex:   '#0000FF'
  },
  {
    id:    2,
    name:  'Nesciunt',
    color: 'green',
    hex:   '#008000'
  },
  {
    id:    3,
    name:  'Orange',
    color: 'orange',
    hex:   '#FFA500'
  },
  {
    id:    4,
    name:  'Adipisci',
    color: 'pink',
    hex:   '#FFC0CB'
  },
  {
    id:    5,
    name:  'Purple',
    color: 'purple',
    hex:   '#800080'
  },
  {
    id:    6,
    name:  'Pariatur',
    color: 'red',
    hex:   '#FF0000'
  },
  {
    id:    7,
    name:  'Yellow',
    color: 'yellow',
    hex:   '#FFFF00'
  }
];

export const stars = fromJS(starsArray);

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

export const filtersCounts = fromJS({});

export const starsCounts = fromJS({});
