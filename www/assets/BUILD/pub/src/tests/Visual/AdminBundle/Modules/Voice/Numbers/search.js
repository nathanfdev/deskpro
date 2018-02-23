import React from 'react';
import Immutable from 'immutable';
import ExistingList from 'DeskPRO/Bundle/AdminBundle/Modules/Voice/Components/Numbers/Search/ExistingNumbers/ExistingList';
import AvailableList from 'DeskPRO/Bundle/AdminBundle/Modules/Voice/Components/Numbers/Search/AvailableNumbers/AvailableList';
import { storiesOf } from '@storybook/react';
import { adminCss } from '../../../../decorators';

const numbers = Immutable.fromJS([
  {
    number: '+44 01403 821213',
    added:  true
  },
  {
    number: '+44 01403 731654'
  },
  {
    number: '+44 01403 777534'
  }
]);

const accounts = Immutable.fromJS([
  {
    id:           1,
    account_name: 'Account 1'
  },
  {
    id:           2,
    account_name: 'Account 2'
  }
]);

const onChangeFilter = (data) => {
  console.log(data);
};

storiesOf('Admin: Twilio', module)
  .addDecorator(story => adminCss(story()))
  .add(
    'Existing numbers list',
    () => <ExistingList numbers={numbers} />
  )
  .add(
    'Existing numbers list w/ multiple accounts',
    () => (
      <ExistingList
        numbers={numbers}
        accounts={accounts}
        onChangeFilter={onChangeFilter}
      />
    )
  )
  .add(
    'Available numbers list w/ multiple accounts',
    () => (
      <AvailableList
        numbers={numbers}
        accounts={accounts}
        onChangeFilter={onChangeFilter}
      />
    )
  )
;
