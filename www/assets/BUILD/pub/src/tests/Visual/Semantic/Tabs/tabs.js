import React from 'react';
import { storiesOf } from '@storybook/react';
import { Tabs } from 'Semantic/Tabs';
import { css } from 'Visual/decorators';

const tabsStruct = {
  items: [
    {
      id:      'first',
      content: 'First',
      title:   'First'
    },
    {
      id:      'second',
      content: 'Second',
      title:   'Second'
    },
    {
      id:      'third',
      content: 'Third',
      title:   'Third'
    }
  ],
  classes: {
    menuItem: ['agent-im-tab-link'],
    item:     ['agent-im-tab']
  }
};

storiesOf('Semantic: tabs', module)
  .addDecorator(story => css(story()))
  .add(
    'Tabs',
    () =>
      <Tabs {...tabsStruct} />
  )
;
