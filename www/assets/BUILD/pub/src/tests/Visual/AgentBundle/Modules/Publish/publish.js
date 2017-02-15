import React from 'react';
import { storiesOf } from '@kadira/storybook'; // eslint-disable-line import/no-extraneous-dependencies
import { css } from 'Visual/decorators';
import { Editor } from 'DeskPRO/Bundle/AgentBundle/Modules/Publish/Components/Editor';

storiesOf('Agent: Publish', module)
  .addDecorator(story => css(story()))
  .add(
    'Agent: Publish: Content Editor',
    () =>
      <Editor />
  )
;
