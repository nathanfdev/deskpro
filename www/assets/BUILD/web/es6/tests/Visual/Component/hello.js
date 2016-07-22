import React from 'react';
import { storiesOf, action } from '@kadira/storybook';
import { Hello } from 'Component/Hello';

storiesOf('App: hello', module)
  .add(
    'Hello Superman',
    () => <Hello person={superman} />
  )
  .add(
    'Hello Batman',
    () => <Hello person={batman} />
  )
;
const batman = {
  fname: 'Bruce',
  lname: 'Wayne'
};
const superman = {
  fname: 'Clark',
  lname: 'Kent'
};