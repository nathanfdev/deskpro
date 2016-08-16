import React from 'react';
import { storiesOf, action, linkTo } from '@kadira/storybook';
import { Toggle, Range } from 'DeskPRO/Component/Semantic/Form';
import { css } from '../../decorators';

storiesOf('Semantic: form', module)
  .addDecorator(story => css(story()))
  .add(
    'Toggle off',
    () => <Toggle onChange={linkTo('Semantic: form', 'Toggle on')}>Label</Toggle>
  )
  .add(
    'Toggle on',
    () => <Toggle active onChange={linkTo('Semantic: form', 'Toggle off')}>Label</Toggle>
  )
  .add(
    'Toggle small off',
    () => <Toggle classes={['small']} onChange={linkTo('Semantic: form', 'Toggle small on')}>Label</Toggle>
  )
  .add(
    'Toggle small on',
    () => <Toggle classes={['small']} active onChange={linkTo('Semantic: form', 'Toggle small off')}>Label</Toggle>
  )
  .add(
    'Range',
    () => <Range min={0} max={20} onChange={action('Range change')} />
  )
;
