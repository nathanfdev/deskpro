import React from 'react';
import { storiesOf, action, linkTo } from '@kadira/storybook';
import { Toggle, Range } from 'Components/Form';

storiesOf('App: form', module)
  .add(
    'Toggle off',
    () => <Toggle onChange={linkTo('App: form', 'Toggle on')}>Label</Toggle>
  )
  .add(
    'Toggle on',
    () => <Toggle active={true} onChange={linkTo('App: form', 'Toggle off')}>Label</Toggle>
  )
  .add(
    'Range',
    () => <Range min={0} max={20} onChange={action('Range change')}/>
  )
;