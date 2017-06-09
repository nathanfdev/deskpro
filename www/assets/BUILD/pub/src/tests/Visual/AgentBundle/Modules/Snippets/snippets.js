import React from 'react';
import { storiesOf } from '@kadira/storybook'; // eslint-disable-line import/no-extraneous-dependencies
import { SnippetsMenu } from 'DeskPRO/Bundle/AgentBundle/Modules/Snippets/Components/SnippetsMenu';
import { snippetsState } from 'DemoState/AgentBundle/Modules/Snippets/snippets';

import { css } from '../../../decorators';

storiesOf('Agent: Snippets', module)
  .addDecorator(story => css(story()))
  .add(
    'Left menu',
    () => <SnippetsMenu snippets={snippetsState} />
  )
;
