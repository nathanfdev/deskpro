import React from 'react';
import { storiesOf } from '@storybook/react';
import NewExtensionList from 'DeskPRO/Bundle/AdminBundle/Modules/Voice/Components/Extensions/NewList/NewExtensionList';
import Immutable from 'immutable';
import { adminCss, redux } from '../../../../decorators';

const agents = Immutable.fromJS([
  {
    id:   1,
    name: 'Agent 1'
  },
  {
    id:   2,
    name: 'Agent 2'
  },
  {
    id:   3,
    name: 'Agent 3'
  },
  {
    id:   4,
    name: 'Agent 4'
  },
  {
    id:   5,
    name: 'Agent 5'
  },
  {
    id:   6,
    name: 'Agent 6'
  },
  {
    id:   7,
    name: 'Agent 7'
  },
  {
    id:   8,
    name: 'Agent 8'
  }
]);

storiesOf('Admin: Twilio', module)
  .addDecorator(story => adminCss(story()))
  .add(
    'Extensions new list',
    () => redux({}, <NewExtensionList agents={agents} />)
  )
;
