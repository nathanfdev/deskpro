import React from 'react';
import { storiesOf, linkTo, action } from '@kadira/storybook';
import Immutable from 'immutable';
import VoiceControls from 'DeskPRO/Bundle/AgentBundle/Modules/Voice/Components/Controls/VoiceControls';
import { css, redux } from '../../../decorators';
import demoState from './demoState';

storiesOf('Agent Voice', module)
  .addDecorator(story => css(story()))
  .add(
    'Voice controls (dialing)',
    () => redux({}, (
      <VoiceControls status="dialing" />
    ))
  )
  .add(
    'Voice controls (connecting)',
    () => redux({}, (
      <VoiceControls status="connecting" />
    ))
  )
  .add(
    'Voice controls (ringing)',
    () => redux({}, (
      <VoiceControls status="ringing" />
    ))
  )
  .add(
    'Voice controls (connected)',
    () => redux({}, (
      <VoiceControls status="connected" />
    ))
  )
  .add(
    'Voice controls (busy)',
    () => redux(demoState, (
      <VoiceControls
        status="busy"
        redial={action('onRedial')}
      />
    ))
  )
  .add(
    'Voice controls (active)',
    () => redux(demoState, (
      <VoiceControls
        status="active"
        onlineAgents={Immutable.fromJS(demoState.RecordsStore.store.Person.records)}
        toggleHold={linkTo('Agent Voice', 'Voice controls (hold)')}
      />
    ))
  )
  .add(
    'Voice controls (hold)',
    () => redux(demoState, (
      <VoiceControls
        status="active"
        hold
        toggleHold={linkTo('Agent Voice', 'Voice controls (active)')}
      />
    ))
  )
;
