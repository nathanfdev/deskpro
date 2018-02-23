import React from 'react';
import Immutable from 'immutable';
import AccountList from 'DeskPRO/Bundle/AdminBundle/Modules/Voice/Components/Accounts/List/AccountList';
import { storiesOf } from '@storybook/react';
import { adminCss } from '../../../../decorators';

const accounts = Immutable.fromJS([
  {
    id:           1,
    account_name: 'Account 1',
    account_sid:  'AC76b4af42af5ceb0c9023f2d3217abce1',
    date_created: '2016-09-19 19:20'
  },
  {
    id:           2,
    account_name: 'Account 2',
    account_sid:  '5fa9b471eb509719cfd7aeda0558fdc9',
    date_created: '2016-09-19 19:20'
  },
  {
    id:           3,
    account_name: 'Account 3',
    account_sid:  '313851deeb4e5fb6c380ae7dc2ed1cf3',
    date_created: '2016-09-19 19:20'
  }
]);

storiesOf('Admin: Twilio', module)
  .addDecorator(story => adminCss(story()))
  .add(
    'Empty account list',
    () => <AccountList />
  )
  .add(
    'Accounts list',
    () => <AccountList accounts={accounts} />
  )
;
