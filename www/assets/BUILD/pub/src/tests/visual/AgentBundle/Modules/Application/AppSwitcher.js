import React from 'react';
import { storiesOf, action } from '@kadira/storybook';
import { decorate } from 'tests/visual/decorate';
import { AppSwitcher } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/AppSwitcher';

window.DP_BASE_URL_RELATIVE = 'demo';
window.DP_AGENT_INTERFACE_PATH_NAMESPACE = 'demo';

storiesOf('App: AppSwitcher', module)
  .addDecorator(story => decorate(story()))
  .add(
    'AppSwitcher',
    () =>
      <AppSwitcher switchApp={action('switchApp')} currentApp="publish" />
  )
;
