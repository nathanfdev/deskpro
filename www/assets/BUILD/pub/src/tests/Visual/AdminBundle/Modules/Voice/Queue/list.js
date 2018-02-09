import React from 'react';
import Immutable from 'immutable';
import QueueList from 'DeskPRO/Bundle/AdminBundle/Modules/Voice/Components/Queues/List/QueueList';
import { storiesOf } from '@storybook/react';
import { adminCss, redux } from '../../../../decorators';

const queues = Immutable.fromJS([
  {
    id:          1,
    name:        'Queue 1',
    greet_asset: {
      name:     'My asset',
      type:     'text',
      text:     'my text',
      language: 'en-GB'
    }
  },
  {
    id:          2,
    name:        'Queue 2',
    greet_asset: {
      name:      'My asset',
      type:      'upload',
      blob_auth: null
    }
  },
  {
    id:          3,
    name:        'Queue 2',
    greet_asset: null
  }
]);

storiesOf('Admin: Twilio', module)
  .addDecorator(story => adminCss(story()))
  .add(
    'Empty queue list',
    () => redux({}, <QueueList />)
  )
  .add(
    'Queue list',
    () => redux({}, <QueueList queues={queues} />)
  )
;
