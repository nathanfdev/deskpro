import React from 'react';
import { storiesOf, action } from '@kadira/storybook'; // eslint-disable-line import/no-extraneous-dependencies
import { css } from 'Visual/decorators';
import { Editor } from 'DeskPRO/Bundle/AgentBundle/Modules/Publish/Components/Editor';
import { ManualTree } from 'DeskPRO/Bundle/AgentBundle/Modules/Publish/Components/List/ManualTree';
import { tree } from 'DemoState/AgentBundle/Modules/Publish/publish';
import 'froala-editor/css/froala_editor.pkgd.css';

storiesOf('Agent: Publish', module)
  .addDecorator(story => css(story()))
  .add(
    'Agent: Publish: Content Editor',
    () =>
      <Editor onAddFile={action('add file')} />
  )
  .add(
    'Agent: Publish: Manual Topic List',
    () =>
      <ManualTree tree={tree} onClick={action('click')} />
  )
;
