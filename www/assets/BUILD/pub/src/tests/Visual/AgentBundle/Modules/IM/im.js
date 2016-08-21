import React from 'react';
import { storiesOf } from '@kadira/storybook';
import IMOverlay from 'DeskPRO/Bundle/AgentBundle/Modules/IM/Components/New/IMOverlay';
import { css } from 'Visual/decorators';

storiesOf('Agent: IM', module)
  .addDecorator(story => css(story()))
  .add(
    'IMOverlay',
    () =>
      <IMOverlay />
  )
;
