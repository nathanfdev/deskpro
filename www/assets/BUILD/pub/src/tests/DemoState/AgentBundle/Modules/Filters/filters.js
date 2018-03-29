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

const labelsArray = {
  'adams ebert and moore':
  {
    text_color: '#000000',
    label_type: 'tickets',
    label:      'adams ebert and moore',
    color:      '#b8f05c',
    total:      0
  },
  'anderson inc':
  {
    text_color: '#FFFFFF',
    label_type: 'tickets',
    label:      'anderson inc',
    color:      '#5870c1',
    total:      0
  },
  'anderson-corkery':
  {
    text_color: '#000000',
    label_type: 'tickets',
    label:      'anderson-corkery',
    color:      '#ea693b',
    total:      0
  },
  'bahringer-tremblay':
  {
    text_color: '#000000',
    label_type: 'tickets',
    label:      'bahringer-tremblay',
    color:      '#98c650',
    total:      0
  },
  'borer greenfelder and feeney':
  {
    text_color: '#FFFFFF',
    label_type: 'tickets',
    label:      'borer greenfelder and feeney',
    color:      '#21718c',
    total:      0
  },
  'borer-kub':
  {
    text_color: '#000000',
    label_type: 'tickets',
    label:      'borer-kub',
    color:      '#ec832e',
    total:      0
  },
  'brakus terry and beer':
  {
    text_color: '#FFFFFF',
    label_type: 'tickets',
    label:      'brakus terry and beer',
    color:      '#c43ddc',
    total:      0
  },
  'braun group':
  {
    text_color: '#000000',
    label_type: 'tickets',
    label:      'braun group',
    color:      '#ffb181',
    total:      0
  },
  'breitenberg-rodriguez':
  {
    text_color: '#FFFFFF',
    label_type: 'tickets',
    label:      'breitenberg-rodriguez',
    color:      '#34ab42',
    total:      0
  },
  'cormier plc':
  {
    text_color: '#FFFFFF',
    label_type: 'tickets',
    label:      'cormier plc',
    color:      '#b83aca',
    total:      0
  },
  'cormier-ritchie':
  {
    text_color: '#FFFFFF',
    label_type: 'tickets',
    label:      'cormier-ritchie',
    color:      '#e70963',
    total:      0
  },
  'crist-kemmer':
  {
    text_color: '#FFFFFF',
    label_type: 'tickets',
    label:      'crist-kemmer',
    color:      '#9c46f0',
    total:      0
  },
  'dach llc':
  {
    text_color: '#FFFFFF',
    label_type: 'tickets',
    label:      'dach llc',
    color:      '#280c32',
    total:      0
  },
  'emmerich-mayert':
  {
    text_color: '#000000',
    label_type: 'tickets',
    label:      'emmerich-mayert',
    color:      '#7ebe79',
    total:      0
  },
  'fahey powlowski and stokes':
  {
    text_color: '#000000',
    label_type: 'tickets',
    label:      'fahey powlowski and stokes',
    color:      '#e2685b',
    total:      0
  },
  'feest-deckow':
  {
    text_color: '#000000',
    label_type: 'tickets',
    label:      'feest-deckow',
    color:      '#c7a808',
    total:      0
  },
  'gleason and sons':
  {
    text_color: '#FFFFFF',
    label_type: 'tickets',
    label:      'gleason and sons',
    color:      '#907c32',
    total:      0
  },
  'goyette nolan and ryan':
  {
    text_color: '#000000',
    label_type: 'tickets',
    label:      'goyette nolan and ryan',
    color:      '#f5d956',
    total:      0
  },
  'graham-corkery':
  {
    text_color: '#FFFFFF',
    label_type: 'tickets',
    label:      'graham-corkery',
    color:      '#e43d53',
    total:      0
  },
  'grant llc':
  {
    text_color: '#FFFFFF',
    label_type: 'tickets',
    label:      'grant llc',
    color:      '#784353',
    total:      0
  },
  'grimes-carroll':
  {
    text_color: '#000000',
    label_type: 'tickets',
    label:      'grimes-carroll',
    color:      '#d96369',
    total:      0
  },
  'gulgowski-ankunding':
  {
    text_color: '#000000',
    label_type: 'tickets',
    label:      'gulgowski-ankunding',
    color:      '#ef7539',
    total:      0
  },
  'hansen daniel and walker':
  {
    text_color: '#FFFFFF',
    label_type: 'tickets',
    label:      'hansen daniel and walker',
    color:      '#61691a',
    total:      0
  },
  'harber group':
  {
    text_color: '#FFFFFF',
    label_type: 'tickets',
    label:      'harber group',
    color:      '#0906f1',
    total:      0
  },
  'harris-schumm':
  {
    text_color: '#000000',
    label_type: 'tickets',
    label:      'harris-schumm',
    color:      '#ed43ce',
    total:      0
  },
  'harvey-becker':
  {
    text_color: '#FFFFFF',
    label_type: 'tickets',
    label:      'harvey-becker',
    color:      '#7c51f9',
    total:      0
  },
  'hayes group':
  {
    text_color: '#000000',
    label_type: 'tickets',
    label:      'hayes group',
    color:      '#f9b0fe',
    total:      0
  },
  'heathcote carroll and schoen':
  {
    text_color: '#000000',
    label_type: 'tickets',
    label:      'heathcote carroll and schoen',
    color:      '#8e8921',
    total:      0
  },
  'hegmann plc':
  {
    text_color: '#000000',
    label_type: 'tickets',
    label:      'hegmann plc',
    color:      '#3fa0a6',
    total:      0
  },
  'heller-sawayn':
  {
    text_color: '#FFFFFF',
    label_type: 'tickets',
    label:      'heller-sawayn',
    color:      '#3d9a7d',
    total:      0
  },
  'hermann-kuhic':
  {
    text_color: '#FFFFFF',
    label_type: 'tickets',
    label:      'hermann-kuhic',
    color:      '#d83367',
    total:      0
  },
  'hermiston o\'reilly and botsford':
  {
    text_color: '#FFFFFF',
    label_type: 'tickets',
    label:      'hermiston o\'reilly and botsford',
    color:      '#a82dbb',
    total:      0
  },
  'hettinger murphy and corkery':
  {
    text_color: '#FFFFFF',
    label_type: 'tickets',
    label:      'hettinger murphy and corkery',
    color:      '#9e2cab',
    total:      0
  },
  'hirthe ryan and hauck':
  {
    text_color: '#000000',
    label_type: 'tickets',
    label:      'hirthe ryan and hauck',
    color:      '#a48876',
    total:      0
  },
  'johns-marks':
  {
    text_color: '#FFFFFF',
    label_type: 'tickets',
    label:      'johns-marks',
    color:      '#6e0b64',
    total:      0
  },
  'kertzmann-lebsack':
  {
    text_color: '#FFFFFF',
    label_type: 'tickets',
    label:      'kertzmann-lebsack',
    color:      '#144993',
    total:      0
  },
  'kessler smith and hyatt':
  {
    text_color: '#FFFFFF',
    label_type: 'tickets',
    label:      'kessler smith and hyatt',
    color:      '#7e0e1e',
    total:      0
  },
  'koss group':
  {
    text_color: '#FFFFFF',
    label_type: 'tickets',
    label:      'koss group',
    color:      '#4d0736',
    total:      0
  },
  'kreiger-kertzmann':
  {
    text_color: '#000000',
    label_type: 'tickets',
    label:      'kreiger-kertzmann',
    color:      '#d3ca03',
    total:      0
  },
  'kuhic-steuber':
  {
    text_color: '#000000',
    label_type: 'tickets',
    label:      'kuhic-steuber',
    color:      '#d88896',
    total:      0
  },
  'kunze-yundt':
  {
    text_color: '#000000',
    label_type: 'tickets',
    label:      'kunze-yundt',
    color:      '#5ce5af',
    total:      0
  },
  'kuphal morar and rice':
  {
    text_color: '#FFFFFF',
    label_type: 'tickets',
    label:      'kuphal morar and rice',
    color:      '#4159de',
    total:      0
  },
  'larkin smith and harber':
  {
    text_color: '#FFFFFF',
    label_type: 'tickets',
    label:      'larkin smith and harber',
    color:      '#b21c76',
    total:      0
  },
  'ledner-prosacco':
  {
    text_color: '#000000',
    label_type: 'tickets',
    label:      'ledner-prosacco',
    color:      '#e65db5',
    total:      0
  },
  'lemke and sons':
  {
    text_color: '#000000',
    label_type: 'tickets',
    label:      'lemke and sons',
    color:      '#9ab860',
    total:      0
  },
  'littel-jaskolski':
  {
    text_color: '#FFFFFF',
    label_type: 'tickets',
    label:      'littel-jaskolski',
    color:      '#8a370f',
    total:      0
  },
  'lowe abshire and stiedemann':
  {
    text_color: '#000000',
    label_type: 'tickets',
    label:      'lowe abshire and stiedemann',
    color:      '#23afda',
    total:      0
  },
  'lowe llc':
  {
    text_color: '#000000',
    label_type: 'tickets',
    label:      'lowe llc',
    color:      '#2dfa8d',
    total:      0
  },
  'lueilwitz inc':
  {
    text_color: '#FFFFFF',
    label_type: 'tickets',
    label:      'lueilwitz inc',
    color:      '#9b1234',
    total:      0
  },
  'macejkovic walter and moen':
  {
    text_color: '#FFFFFF',
    label_type: 'tickets',
    label:      'macejkovic walter and moen',
    color:      '#732a89',
    total:      0
  },
  'mante erdman and berge':
  {
    text_color: '#000000',
    label_type: 'tickets',
    label:      'mante erdman and berge',
    color:      '#cf6f86',
    total:      0
  },
  'marquardt llc':
  {
    text_color: '#FFFFFF',
    label_type: 'tickets',
    label:      'marquardt llc',
    color:      '#ab31e8',
    total:      0
  },
  'mayer llc':
  {
    text_color: '#FFFFFF',
    label_type: 'tickets',
    label:      'mayer llc',
    color:      '#5d0f14',
    total:      0
  },
  'mayert inc':
  {
    text_color: '#000000',
    label_type: 'tickets',
    label:      'mayert inc',
    color:      '#edee9a',
    total:      0
  },
  'mertz-shields':
  {
    text_color: '#FFFFFF',
    label_type: 'tickets',
    label:      'mertz-shields',
    color:      '#b033c0',
    total:      0
  },
  'metz-ledner':
  {
    text_color: '#000000',
    label_type: 'tickets',
    label:      'metz-ledner',
    color:      '#f45cb9',
    total:      0
  },
  'mosciski ltd':
  {
    text_color: '#FFFFFF',
    label_type: 'tickets',
    label:      'mosciski ltd',
    color:      '#db3c7e',
    total:      0
  },
  'mraz-tillman':
  {
    text_color: '#FFFFFF',
    label_type: 'tickets',
    label:      'mraz-tillman',
    color:      '#93409b',
    total:      0
  },
  'nader-larson':
  {
    text_color: '#FFFFFF',
    label_type: 'tickets',
    label:      'nader-larson',
    color:      '#ed1d00',
    total:      0
  },
  'o\'keefe-kutch':
  {
    text_color: '#000000',
    label_type: 'tickets',
    label:      'o\'keefe-kutch',
    color:      '#80b2c7',
    total:      0
  },
  'o\'kon kessler and larkin':
  {
    text_color: '#FFFFFF',
    label_type: 'tickets',
    label:      'o\'kon kessler and larkin',
    color:      '#e207ff',
    total:      0
  },
  'oberbrunner-prosacco':
  {
    text_color: '#FFFFFF',
    label_type: 'tickets',
    label:      'oberbrunner-prosacco',
    color:      '#8a4df6',
    total:      0
  },
  'ortiz ltd':
  {
    text_color: '#000000',
    label_type: 'tickets',
    label:      'ortiz ltd',
    color:      '#ddd686',
    total:      0
  },
  'pagac-collins':
  {
    text_color: '#FFFFFF',
    label_type: 'tickets',
    label:      'pagac-collins',
    color:      '#fc392f',
    total:      0
  },
  'parisian ltd':
  {
    text_color: '#FFFFFF',
    label_type: 'tickets',
    label:      'parisian ltd',
    color:      '#358bb2',
    total:      0
  },
  'reichel and sons':
  {
    text_color: '#FFFFFF',
    label_type: 'tickets',
    label:      'reichel and sons',
    color:      '#6d445a',
    total:      0
  },
  'reynolds schuppe and schmitt':
  {
    text_color: '#FFFFFF',
    label_type: 'tickets',
    label:      'reynolds schuppe and schmitt',
    color:      '#369418',
    total:      0
  },
  'rice group':
  {
    text_color: '#000000',
    label_type: 'tickets',
    label:      'rice group',
    color:      '#7e9ec2',
    total:      0
  },
  'rutherford-kilback':
  {
    text_color: '#FFFFFF',
    label_type: 'tickets',
    label:      'rutherford-kilback',
    color:      '#825ed6',
    total:      0
  },
  'sawayn-jaskolski':
  {
    text_color: '#FFFFFF',
    label_type: 'tickets',
    label:      'sawayn-jaskolski',
    color:      '#da2c09',
    total:      0
  },
  'schimmel weissnat and kerluke':
  {
    text_color: '#000000',
    label_type: 'tickets',
    label:      'schimmel weissnat and kerluke',
    color:      '#d9d5f6',
    total:      0
  },
  'schmidt upton and auer':
  {
    text_color: '#000000',
    label_type: 'tickets',
    label:      'schmidt upton and auer',
    color:      '#bc4de2',
    total:      0
  },
  'schneider plc':
  {
    text_color: '#FFFFFF',
    label_type: 'tickets',
    label:      'schneider plc',
    color:      '#298094',
    total:      0
  },
  'schumm kuvalis and grady':
  {
    text_color: '#000000',
    label_type: 'tickets',
    label:      'schumm kuvalis and grady',
    color:      '#3ce790',
    total:      0
  },
  'skiles-bradtke':
  {
    text_color: '#000000',
    label_type: 'tickets',
    label:      'skiles-bradtke',
    color:      '#4ef8eb',
    total:      0
  },
  'stark-purdy':
  {
    text_color: '#FFFFFF',
    label_type: 'tickets',
    label:      'stark-purdy',
    color:      '#f411da',
    total:      0
  },
  'stehr-funk':
  {
    text_color: '#000000',
    label_type: 'tickets',
    label:      'stehr-funk',
    color:      '#89c082',
    total:      0
  },
  'stiedemann llc':
  {
    text_color: '#000000',
    label_type: 'tickets',
    label:      'stiedemann llc',
    color:      '#9b9347',
    total:      0
  },
  'streich and sons':
  {
    text_color: '#000000',
    label_type: 'tickets',
    label:      'streich and sons',
    color:      '#d7bae3',
    total:      0
  },
  'streich-hyatt':
  {
    text_color: '#000000',
    label_type: 'tickets',
    label:      'streich-hyatt',
    color:      '#57cb15',
    total:      0
  },
  'strosin-strosin':
  {
    text_color: '#000000',
    label_type: 'tickets',
    label:      'strosin-strosin',
    color:      '#4ccffa',
    total:      0
  },
  'terry group':
  {
    text_color: '#FFFFFF',
    label_type: 'tickets',
    label:      'terry group',
    color:      '#074ca2',
    total:      0
  },
  'torp armstrong and wyman':
  {
    text_color: '#000000',
    label_type: 'tickets',
    label:      'torp armstrong and wyman',
    color:      '#907afe',
    total:      0
  },
  'torphy-cormier':
  {
    text_color: '#FFFFFF',
    label_type: 'tickets',
    label:      'torphy-cormier',
    color:      '#3c5adf',
    total:      0
  },
  'towne-mccullough':
  {
    text_color: '#000000',
    label_type: 'tickets',
    label:      'towne-mccullough',
    color:      '#4ba094',
    total:      0
  },
  'toy plc':
  {
    text_color: '#FFFFFF',
    label_type: 'tickets',
    label:      'toy plc',
    color:      '#651d97',
    total:      0
  },
  'treutel lynch and mayer':
  {
    text_color: '#000000',
    label_type: 'tickets',
    label:      'treutel lynch and mayer',
    color:      '#f0f1cf',
    total:      0
  },
  'vonrueden baumbach and bartell':
  {
    text_color: '#FFFFFF',
    label_type: 'tickets',
    label:      'vonrueden baumbach and bartell',
    color:      '#0cc61a',
    total:      0
  },
  'vonrueden plc':
  {
    text_color: '#000000',
    label_type: 'tickets',
    label:      'vonrueden plc',
    color:      '#39cff6',
    total:      0
  },
  'ward ltd':
  {
    text_color: '#FFFFFF',
    label_type: 'tickets',
    label:      'ward ltd',
    color:      '#374352',
    total:      0
  },
  'ward plc':
  {
    text_color: '#FFFFFF',
    label_type: 'tickets',
    label:      'ward plc',
    color:      '#295e2e',
    total:      0
  },
  'waters and sons':
  {
    text_color: '#000000',
    label_type: 'tickets',
    label:      'waters and sons',
    color:      '#e9f876',
    total:      0
  },
  'white-blanda':
  {
    text_color: '#FFFFFF',
    label_type: 'tickets',
    label:      'white-blanda',
    color:      '#fe1edf',
    total:      0
  },
  'will-wiegand':
  {
    text_color: '#FFFFFF',
    label_type: 'tickets',
    label:      'will-wiegand',
    color:      '#0b275e',
    total:      0
  },
  'williamson group':
  {
    text_color: '#FFFFFF',
    label_type: 'tickets',
    label:      'williamson group',
    color:      '#552626',
    total:      0
  },
  'wintheiser torp and windler':
  {
    text_color: '#000000',
    label_type: 'tickets',
    label:      'wintheiser torp and windler',
    color:      '#8699fa',
    total:      0
  },
  'wisoky plc':
  {
    text_color: '#FFFFFF',
    label_type: 'tickets',
    label:      'wisoky plc',
    color:      '#6b51d3',
    total:      0
  },
  'wuckert-hagenes':
  {
    text_color: '#000000',
    label_type: 'tickets',
    label:      'wuckert-hagenes',
    color:      '#c4eafa',
    total:      0
  },
  'yundt inc':
  {
    text_color: '#000000',
    label_type: 'tickets',
    label:      'yundt inc',
    color:      '#7bb481',
    total:      0
  },
  'zieme-johnston':
  {
    text_color: '#FFFFFF',
    label_type: 'tickets',
    label:      'zieme-johnston',
    color:      '#9508e0',
    total:      0
  }
};

export const labels = fromJS(labelsArray);
console.log(labels);

export const filtersCounts = fromJS({});

export const starsCounts = fromJS({});
