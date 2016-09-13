import React from 'react';
import { storiesOf } from '@kadira/storybook';
import { SideBar } from 'DeskPRO/Bundle/AgentBundle/Modules/SideBar/SideBar';
import { css } from '../../../decorators';

storiesOf('App: side bar', module)
  .addDecorator(story => css(story()))
  .add(
    'Side bar',
    () => <div>
      <SideBar />
    </div>
  )
;
