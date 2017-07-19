import React from 'react';
import { storiesOf } from '@kadira/storybook'; // eslint-disable-line import/no-extraneous-dependencies
import { SnippetsMenu } from 'DeskPRO/Bundle/AgentBundle/Modules/Snippets/Components/SnippetsMenu';
import { SnippetsModal } from 'DeskPRO/Bundle/AgentBundle/Modules/Snippets/Components/SnippetsModal';
import { snippetsState, editSnippet, editTranslation, languages, me } from 'DemoState/AgentBundle/Modules/Snippets/snippets';

import { css } from '../../../decorators';

storiesOf('Agent: Snippets', module)
  .addDecorator(story => css(story()))
  .add(
    'Left menu',
    () => <SnippetsMenu snippets={snippetsState} langId={1} me={me} langPref={[1, 2, 3]} width={700} languages={languages} />
  )
  .add(
    'Modal',
    () => <SnippetsModal snippet={editSnippet} translation={editTranslation} languages={languages} langId={1} />
  )
;
