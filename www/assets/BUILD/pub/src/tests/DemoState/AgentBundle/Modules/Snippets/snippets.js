import Immutable from 'immutable';

const messageOne = {
  id:            1,
  title:         'Admin manual',
  shortcut_code: 'admin',
  labels:        ['General', 'DeskPRO Cloud'],
  lang:          ['en', 'es', 'de'],
  translations:  [
    {
      id:       1,
      snippet:  1,
      language: 1,
      content:  'here',
      title:    'alias et aut',
      blobs:    []
    }
  ]
};
const messageTwo = {
  id:            2,
  title:         'AGENT manual',
  shortcut_code: 'agent',
  labels:        ['General', 'DeskPRO Cloud', 'Agents'],
  lang:          ['en', 'es'],
  translations:  [
    {
      id:       1,
      snippet:  1,
      language: 1,
      content:  'here',
      title:    'alias et aut',
      blobs:    []
    }
  ]
};
const messageThree = {
  id:            3,
  title:         'API docs',
  shortcut_code: 'api',
  labels:        ['Docs'],
  lang:          ['en', 'es', 'de'],
  translations:  [
    {
      id:       1,
      snippet:  1,
      language: 1,
      content:  'here',
      title:    'alias et aut',
      blobs:    []
    }
  ]
};
const messageFour = {
  id:            4,
  title:         'Price Quote',
  shortcut_code: 'price',
  labels:        ['General', 'Sales'],
  lang:          ['en', 'es', 'de'],
  translations:  [
    {
      id:       1,
      snippet:  1,
      language: 1,
      content:  'We are very transparent about pricing and prices are as shown as on the content is longer to check emphasis',
      title:    'alias et aut',
      blobs:    []
    }
  ]
};
const messageFive = {
  id:            5,
  title:         'Delete Agent',
  shortcut_code: 'deleteagent',
  labels:        ['General'],
  lang:          ['en', 'es'],
  translations:  [
    {
      id:       1,
      snippet:  1,
      language: 1,
      content:  'When you delete an agent you can choose from one of two option',
      title:    'alias et aut',
      blobs:    []
    }
  ]
};
const messageSix = {
  id:            6,
  title:         'SSL for custom Portal/Chat',
  shortcut_code: 'SSL',
  labels:        ['Docs'],
  lang:          ['en'],
  translations:  [
    {
      id:       1,
      snippet:  1,
      language: 1,
      content:  'Sure -- we can install a custom SSL cert for you on your own domain',
      title:    'alias et aut',
      blobs:    []
    }
  ]
};
const messageSeven = {
  id:            7,
  title:         'Where is data hosted?',
  draft:         true,
  shortcut_code: 'datahost',
  labels:        ['General'],
  lang:          ['en', 'es', 'de'],
  translations:  [
    {
      id:       1,
      snippet:  1,
      language: 1,
      content:  'In the US in secure dedicated data centres. You can choose EU hosting',
      title:    'alias et aut',
      blobs:    []
    }
  ]
};

export const snippetsState = Immutable.fromJS([
  messageOne,
  messageTwo,
  messageThree,
  messageFour,
  messageFive,
  messageSix,
  messageSeven
]);

export const editSnippet = Immutable.fromJS({
  id:     3,
  title:  'eum cum necessitatibus',
  person: null,
  types:  [
    'ticket'
  ],
  shortcut_code: 'natus maxime',
  labels:        [
    'enim ullam aut'
  ],
  translations: [
    {
      id:       3,
      snippet:  3,
      language: 1,
      content:  "Alice had no very clear notion how delightful it will be the right words,' said poor Alice, who felt very glad to find my way into a pig, and she ran with all.",
      title:    'eum cum necessitatibus',
      blobs:    []
    }
  ],
  is_draft: false
});

