import React from 'react';
import Immutable from 'immutable';
import NumberList from 'DeskPRO/Bundle/AdminBundle/Modules/Voice/Components/Numbers/List/NumberList';
import { storiesOf } from '@storybook/react';
import { adminCss, redux } from '../../../../decorators';

const numbers = Immutable.fromJS([
  {
    id:            1,
    number:        '+44 01403 731654',
    nickname:      'Sales Team',
    country_code:  'us',
    queue_targets: ['1', '2', '3', '5', '6', '7']
  },
  {
    id:            2,
    number:        '+44 01403 732555',
    nickname:      'Support Team',
    country_code:  'gb',
    queue_targets: []
  }
]);

const queues = Immutable.fromJS({
  1: {
    id:   '1',
    name: 'Queue 1'
  },
  2: {
    id:   '2',
    name: 'Queue 2'
  },
  4: {
    id:   '4',
    name: 'Queue 4'
  },
  5: {
    id:   '5',
    name: 'Queue 5'
  },
  6: {
    id:   '6',
    name: 'Queue 6'
  },
  7: {
    id:   '7',
    name: 'Queue 7'
  }
});


storiesOf('Admin: Twilio', module)
  .addDecorator(story => adminCss(story()))
  .add(
    'Empty number list',
    () => <NumberList />
  )
  .add(
    'Number list',
    () => redux({}, <NumberList numbers={numbers} queues={queues} />)
  )
;
