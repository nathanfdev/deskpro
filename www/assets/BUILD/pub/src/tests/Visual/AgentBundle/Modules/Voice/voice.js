import React from 'react';
import { storiesOf, action } from '@storybook/react';
import Immutable from 'immutable';
import VoiceMenuDropdown from 'DeskPRO/Bundle/AgentBundle/Modules/Voice/Components/VoiceMenu/VoiceMenuDropdown';
import { css, redux } from '../../../decorators';
import demoState from './demoState';

storiesOf('Agent Voice', module)
  .addDecorator(story => css(story()))
  .add(
    'Dialpad',
    () => redux(demoState, (
      <div style={{ position: 'absolute', left: '500px' }}>
        <VoiceMenuDropdown
          onChangeSettings={action('onChangeSettings')}
        />
      </div>
    ))
  )
  .add(
    'Unknown incoming call',
    () => redux(demoState, (
      <div style={{ position: 'absolute', left: '500px' }}>
        <VoiceMenuDropdown
          me={Immutable.fromJS(demoState.RecordsStore.store.Person.records[1])}
          incomingCall={Immutable.fromJS({ number: '' })}
          onChangeSettings={action('onChangeSettings')}
          onAcceptCall={action('onAcceptCall')}
          onDeclineCall={action('onDeclineCall')}
        />
      </div>
    ))
  )
  .add(
    'Person incoming call',
    () => redux(demoState, (
      <div style={{ position: 'absolute', left: '500px' }}>
        <VoiceMenuDropdown
          me={Immutable.fromJS(demoState.RecordsStore.store.Person.records[1])}
          incomingCall={Immutable.fromJS({ number: '+44 2392 657421' })}
          onChangeSettings={action('onChangeSettings')}
          onAcceptCall={action('onAcceptCall')}
          onDeclineCall={action('onDeclineCall')}
        />
      </div>
    ))
  )
  .add(
    'Agent w/o avatar',
    () => redux(demoState, (
      <div style={{ position: 'absolute', left: '500px' }}>
        <VoiceMenuDropdown
          me={Immutable.fromJS(demoState.RecordsStore.store.Person.records[5])}
          incomingCall={Immutable.fromJS({ number: '+44 2392 657421' })}
          onChangeSettings={action('onChangeSettings')}
          onAcceptCall={action('onAcceptCall')}
          onDeclineCall={action('onDeclineCall')}
        />
      </div>
    ))
  )
  .add(
    'Incoming call invite (add)',
    () => redux(demoState, (
      <div style={{ position: 'absolute', left: '500px' }}>
        <VoiceMenuDropdown
          me={Immutable.fromJS(demoState.RecordsStore.store.Person.records[5])}
          incomingCall={Immutable.fromJS({ number: '+44 2392 657421', call_id: 1, call_type: 'add' })}
          onChangeSettings={action('onChangeSettings')}
          onAcceptCall={action('onAcceptCall')}
          onDeclineCall={action('onDeclineCall')}
        />
      </div>
    ))
  )
  .add(
    'Incoming call invite (transfer)',
    () => redux(demoState, (
      <div style={{ position: 'absolute', left: '500px' }}>
        <VoiceMenuDropdown
          me={Immutable.fromJS(demoState.RecordsStore.store.Person.records[5])}
          incomingCall={Immutable.fromJS({ number: '+44 2392 657421', call_id: 1, call_type: 'transfer' })}
          onChangeSettings={action('onChangeSettings')}
          onAcceptCall={action('onAcceptCall')}
          onDeclineCall={action('onDeclineCall')}
        />
      </div>
    ))
  )
;
