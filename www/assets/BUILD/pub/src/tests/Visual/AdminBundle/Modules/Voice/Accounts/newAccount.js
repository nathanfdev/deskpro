import React from 'react';
import Immutable from 'immutable';
import { storiesOf } from '@storybook/react';
import AccountForm from 'DeskPRO/Bundle/AdminBundle/Modules/Voice/Components/Accounts/Form/AccountForm';
import { adminCss } from '../../../../decorators';

const testAccount = Immutable.fromJS({
  id:           1,
  account_name: 'Test Account',
  account_id:   '313851deeb4e5fb6c380ae7dc2ed1cf3',
  auth_token:   '313851deeb4e5fb6c380ae7dc2ed1cf3'
});

storiesOf('Admin: Twilio', module)
  .addDecorator(story => adminCss(story()))
  .add(
    'New account form',
    () => <AccountForm onSubmit={data => console.log(data)} />
  )
  .add(
    'New account saving',
    () =>
      <AccountForm
        saving
        account={testAccount}
        onSubmit={data => console.log(data)}
      />
  )
  .add(
    'Edit account form',
    () =>
      <AccountForm
        account={testAccount}
        onSubmit={data => console.log(data)}
      />
  )
;
