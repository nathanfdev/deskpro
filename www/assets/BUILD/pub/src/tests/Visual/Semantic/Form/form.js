import React from 'react';
import { storiesOf, action, linkTo } from '@kadira/storybook';
import { Toggle, Range } from 'DeskPRO/Component/Semantic/Form';

storiesOf('Semantic: form', module)
  .add(
    'Toggle off',
    () => <Toggle onChange={linkTo('Semantic: form', 'Toggle on')}>Label</Toggle>
  )
  .add(
    'Toggle on',
    () => <Toggle active onChange={linkTo('Semantic: form', 'Toggle off')}>Label</Toggle>
  )
  .add(
    'Range',
    () => <Range min={0} max={20} onChange={action('Range change')} />
  )
;
