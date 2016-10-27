import React from 'react';
import { storiesOf, linkTo, action } from '@kadira/storybook';
import TicketHeader from 'DeskPRO/Bundle/AgentBundle/Modules/Voice/Components/TicketHeader/TicketHeader';
import { css, redux } from '../../../decorators';
import demoState from './demoState';

storiesOf('Agent Voice', module)
  .addDecorator(story => css(story()))
  .add(
    'Ticket header (dialing)',
    () => redux({}, (
      <TicketHeader status="dial" />
    ))
  )
  .add(
    'Ticket header (connecting)',
    () => redux({}, (
      <TicketHeader status="connect" />
    ))
  )
  .add(
    'Ticket header (ringing)',
    () => redux({}, (
      <TicketHeader status="ring" />
    ))
  )
  .add(
    'Ticket header (connected)',
    () => redux({}, (
      <TicketHeader status="connected" />
    ))
  )
  .add(
    'Ticket header (busy)',
    () => redux(demoState, (
      <TicketHeader
        status="busy"
        onRedial={action('onRedial')}
      />
    ))
  )
  .add(
    'Ticket header (active)',
    () => redux(demoState, (
      <TicketHeader
        status="active"
        onHold={linkTo('Agent Voice', 'Ticket header (hold)')}
      />
    ))
  )
  .add(
    'Ticket header (hold)',
    () => redux(demoState, (
      <TicketHeader
        status="active"
        hold
        onHold={linkTo('Agent Voice', 'Ticket header (active)')}
      />
    ))
  )
;
