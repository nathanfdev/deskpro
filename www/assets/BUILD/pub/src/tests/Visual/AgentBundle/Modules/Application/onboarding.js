import React from 'react';
import { storiesOf, action } from '@storybook/react';
import Immutable from 'immutable';
import { AgentOnboarding } from 'DeskPRO/Bundle/AgentBundle/Modules/Onboarding/Components/AgentOnboarding';
import { css, redux } from '../../../decorators';

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
    ],
    intro: {
      title:  'New DeskPRO update',
      text:   'We’ve made a few changes to how you navigate DeskPRO. Let’s take a quick look...',
      action: 'Start'
    }
  }
});

storiesOf('App: onboarding', module)
  .addDecorator(story => css(story()))
  .addDecorator(story => redux({}, story()))
  .add(
    'Simple', () =>
      <div>
        <AgentOnboarding
          onboarding={basicOnboarding}
          updateCurrentStep={action('updateStep')}
          pauseOnboarding={action('pause')}
          resumeOnboarding={action('resume')}
        />
        <p>
          <button className="button1">Button 1</button>
        </p>
        <p>
          <button className="button2">Button 2</button>
        </p>
      </div>
  )
;
