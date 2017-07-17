import Immutable from 'immutable';

const snippetOne = {
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
const snippetTwo = {
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
const snippetThree = {
  id:            3,
  title:         'API docs',
  shortcut_code: 'api',
  labels:        ['Docs', 'General / Sublabel'],
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
const snippetFour = {
  id:            4,
  title:         'Price Quote',
  shortcut_code: 'price',
  labels:        ['General', 'Sales', 'General/Test/Deep'],
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
const snippetFive = {
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
const snippetSix = {
  id:            6,
  title:         'SSL for custom Portal/Chat',
  shortcut_code: 'SSL',
  labels:        ['Docs', 'After Sales'],
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
const snippetSeven = {
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
  snippetOne,
  snippetTwo,
  snippetThree,
  snippetFour,
  snippetFive,
  snippetSix,
  snippetSeven
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

export const editTranslation = Immutable.fromJS({
  id:       1,
  snippet:  1,
  language: 1,
  content:  'In the US in secure dedicated data centres. You can choose EU hosting',
  title:    'alias et aut',
  blobs:    []
});

export const languages = Immutable.fromJS([
  {
    has_agent:  true,
    flag_image: 'http://deskpro5.local/assets/BUILD/web/images/flags/us.png',
    has_admin:  true,
    has_user:   true,
    is_rtl:     false,
    title:      'English',
    locale:     'en_US',
    id:         1,
    sys_name:   'default',
    lang_code:  'eng'
  },
  {
    has_agent:  true,
    flag_image: 'http://deskpro5.local/assets/BUILD/web/images/flags/us.png',
    has_admin:  true,
    has_user:   true,
    is_rtl:     false,
    title:      'Dev Blank Out',
    locale:     'en_T1',
    id:         2,
    sys_name:   'dev_blankout',
    lang_code:  'eng'
  },
  {
    has_agent:  true,
    flag_image: 'http://deskpro5.local/assets/BUILD/web/images/flags/us.png',
    has_admin:  true,
    has_user:   true,
    is_rtl:     false,
    title:      'Dev Long String',
    locale:     'en_T2',
    id:         3,
    sys_name:   'dev_longstring',
    lang_code:  'eng'
  },
  {
    has_agent:  true,
    flag_image: 'http://deskpro5.local/assets/BUILD/web/images/flags/us.png',
    has_admin:  true,
    has_user:   true,
    is_rtl:     true,
    title:      'Dev RTL',
    locale:     'en_T3',
    id:         4,
    sys_name:   'dev_rtl',
    lang_code:  'eng'
  },
  {
    has_agent:  true,
    flag_image: 'http://deskpro5.local/assets/BUILD/web/images/flags/arabic.png',
    has_admin:  true,
    has_user:   true,
    is_rtl:     true,
    title:      'العربية',
    locale:     'ar',
    id:         5,
    sys_name:   'arabic',
    lang_code:  'ara'
  },
  {
    has_agent:  true,
    flag_image: 'http://deskpro5.local/assets/BUILD/web/images/flags/fr.png',
    has_admin:  false,
    has_user:   true,
    is_rtl:     false,
    title:      'Français',
    locale:     'fr',
    id:         6,
    sys_name:   'french',
    lang_code:  'fre'
  }
]);
export const me = Immutable.fromJS({
  id:    1,
  name:  'John Doe',
  teams: [],
});
