import React from 'react';
import { storiesOf, action } from '@kadira/storybook';
import Immutable from 'immutable';
import VoiceMenuDropdown from 'DeskPRO/Bundle/AgentBundle/Modules/Voice/Components/VoiceMenu/VoiceMenuDropdown';
import { css, redux } from '../../../decorators';
import demoState from './demoState';

storiesOf('Agent Voice', module)
  .addDecorator(story => css(story()))
  .add(
    'Dialpad',
    () => redux(demoState, (
      <VoiceMenuDropdown
        onChangeSettings={action('onChangeSettings')}
      />
    ))
  )
  .add(
    'Unknown incoming call',
    () => redux(demoState, (
      <VoiceMenuDropdown
        callFrom={{}}
        callTarget={Immutable.fromJS({ type: 'queue', queue: 1 })}
        onChangeSettings={action('onChangeSettings')}
        onAcceptCall={action('onAcceptCall')}
        onDeclineCall={action('onDeclineCall')}
      />
    ))
  )
  .add(
    'Person incoming call',
    () => redux(demoState, (
      <VoiceMenuDropdown
        callFrom={{ number: '+44 2392 657421', person: '2' }}
        callTarget={Immutable.fromJS({ type: 'agent', agent: '1' })}
        onChangeSettings={action('onChangeSettings')}
        onAcceptCall={action('onAcceptCall')}
        onDeclineCall={action('onDeclineCall')}
      />
    ))
  )
;
