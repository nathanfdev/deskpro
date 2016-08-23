import React from 'react';
import { storiesOf } from '@kadira/storybook';
import { css } from '../../../decorators';
import Immutable from 'immutable';
import { AgentOnboarding } from 'DeskPRO/Bundle/AgentBundle/Modules/Onboarding/Components/AgentOnboarding';
// import { basicOnboarding } from 'DemoState/AgentBundle/Modules/Application/notifications';

const basicOnboarding = Immutable.Map({
  config: {
    force: false,

    steps: [
      {
        title:    'Trigger Action',
        text:     'Test Onboarding',
        selector: '.button1',
        position: 'bottom'
      },
      {
        title:    'Notifications',
        text:     'Here are now the notifications',
        selector: '.button2',
        position: 'bottom'
      }
    ]
  }
});

storiesOf('App: onboarding', module)
  .addDecorator(story => css(story()))
  .add(
    'Simple', () =>
      <div>
        <AgentOnboarding onboarding={basicOnboarding} />
        <p>
          <button className="button1">Button 1</button>
        </p>
        <p>
          <button className="button2">Button 2</button>
        </p>
      </div>
  )
;
