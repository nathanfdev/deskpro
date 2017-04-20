import React from 'react';
import { storiesOf, action, linkTo } from '@kadira/storybook';
import { Toggle, Range, Select } from 'DeskPRO/Component/Semantic/Form';
import { css } from 'Visual/decorators';
import { Button, ButtonGroup } from 'DeskPRO/Component/Semantic/Button';

const options = [
  {
    label: 'Male',
    value: 1
  },
  {
    label: 'Female',
    value: 2
  }
];
const optionsColours = [
  {
    label: 'Blue',
    value: 'blue'
  },
  {
    label: 'Brown',
    value: 'brown'
  },
  {
    label: 'Green',
    value: 'green'
  },
  {
    label: 'Orange',
    value: 'orange'
  },
  {
    label: 'Pink',
    value: 'pink'
  },
  {
    label: 'Red',
    value: 'red'
  },
  {
    label: 'Yellow',
    value: 'yellow'
  }
];
storiesOf('Semantic: form', module)
  .addDecorator(story => css(story()))
  .add(
    'Toggle off', () =>
      <Toggle onChange={linkTo('Semantic: form', 'Toggle on')}>Label</Toggle>
  )
  .add(
    'Toggle on', () =>
      <Toggle active onChange={linkTo('Semantic: form', 'Toggle off')}>Label</Toggle>
  )
  .add(
    'Toggle small off', () =>
      <Toggle className="small" onChange={linkTo('Semantic: form', 'Toggle small on')}>Label</Toggle>
  )
  .add(
    'Toggle small on', () =>
      <Toggle className="small" active onChange={linkTo('Semantic: form', 'Toggle small off')}>Label</Toggle>
  )
  .add(
    'Range', () =>
      <Range min={0} max={20} onChange={action('Range change')} />
  )
  .add(
    'Select', () =>
      <Select options={options} onChange={action('Range change')} placeholder="Gender" />
  )
  .add(
    'Select with filter', () =>
      <Select options={optionsColours} onChange={action('Range change')} placeholder="Colour" filter />
  )
  .add(
    'Checkbox off',
    () => <Toggle checkbox onChange={linkTo('Semantic: form', 'Checkbox on')}>Label</Toggle>
  )
  .add(
    'Checkbox on',
    () => <Toggle active checkbox onChange={linkTo('Semantic: form', 'Checkbox off')}>Label</Toggle>
  )
  .add(
    'Button Group',
    () => <ButtonGroup>
      <Button onClick={action('Click First')} key="1" className="test">First</Button>
      <Button onClick={action('Click Second')} key="2">Second</Button>
      <Button onClick={action('Click Third')} key="3">Third</Button>
    </ButtonGroup>
  )
;
