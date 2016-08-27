import React from 'react';
import { storiesOf } from '@kadira/storybook';
import IMOverlay from 'DeskPRO/Bundle/AgentBundle/Modules/IM/Components/New/IMOverlay';
import { css } from 'Visual/decorators';
import { imState } from 'DemoState/AgentBundle/Modules/IM/im';

storiesOf('Agent: IM', module)
  .addDecorator(story => css(story()))
  .add(
    'IMOverlay',
    () =>
      <IMOverlay {...imState} />
  )
;
