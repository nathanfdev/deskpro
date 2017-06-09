import React from 'react';
import Immutable from 'immutable';

const messageOne = {
  id:            1,
  title:         'Admin manual',
  shortcut_code: 'admin',
  labels:        ['General', 'DeskPRO Cloud'],
  lang:          ['en', 'es', 'de'],
  content:       <a>here</a>
};
const messageTwo = {
  id:            2,
  title:         'AGENT manual',
  shortcut_code: 'agent',
  labels:        ['General', 'DeskPRO Cloud', 'Agents'],
  lang:          ['en', 'es'],
  content:       <a>here</a>
};
const messageThree = {
  id:            3,
  title:         'API docs',
  shortcut_code: 'api',
  labels:        ['Docs'],
  lang:          ['en', 'es', 'de'],
  content:       <a>here</a>
};
const messageFour = {
  id:            4,
  title:         'Price Quote',
  shortcut_code: 'price',
  labels:        ['General', 'Sales'],
  lang:          ['en', 'es', 'de'],
  content:       'We are very transparent about pricing and prices are as shown as on the content is longer to check emphasis'
};
const messageFive = {
  id:            5,
  title:         'Delete Agent',
  shortcut_code: 'deleteagent',
  labels:        ['General'],
  lang:          ['en', 'es'],
  content:       'When you delete an agent you can choose from one of two option'
};
const messageSix = {
  id:            6,
  title:         'SSL for custom Portal/Chat',
  shortcut_code: 'SSL',
  labels:        ['Docs'],
  lang:          ['en'],
  content:       'Sure -- we can install a custom SSL cert for you on your own domain'
};
const messageSeven = {
  id:            7,
  title:         'Where is data hosted?',
  draft:         true,
  shortcut_code: 'datahost',
  labels:        ['General'],
  lang:          ['en', 'es', 'de'],
  content:       'In the US in secure dedicated data centres. You can choose EU hosting'
};

export const snippetsState = Immutable.Map({
  snippets: Immutable.fromJS([
    messageOne,
    messageTwo,
    messageThree,
    messageFour,
    messageFive,
    messageSix,
    messageSeven
  ])
});
