import React from 'react';
import { storiesOf, action } from '@storybook/react';
import { AppSwitcher } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/AppSwitcher';
import { css } from '../../../decorators';

window.DP_BASE_URL_RELATIVE = 'demo';
window.DP_AGENT_INTERFACE_PATH_NAMESPACE = 'demo';

storiesOf('App: AppSwitcher', module)
  .addDecorator(story => css(story()))
  .add(
    'AppSwitcher',
    () =>
      <AppSwitcher switchApp={action('switchApp')} currentApp="publish" />
  )
;
