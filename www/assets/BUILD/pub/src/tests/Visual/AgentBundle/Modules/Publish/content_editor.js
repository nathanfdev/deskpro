import React from 'react';
import { storiesOf, action } from '@storybook/react';
import { css, redux } from '../../../decorators';
import ContentEditor from '../../../../../DeskPRO/Bundle/AgentBundle/Modules/Publish/Components/Content/ContentEditor';

storiesOf('App: content Editor', module)
  .addDecorator(story => css(story()))
  .addDecorator(story => redux({}, story()))
  .add(
    'Simple', () =>
      <div>
        <ContentEditor
          value="Test value"
          onChange={action('onChange')}
        />
      </div>
  )
;
