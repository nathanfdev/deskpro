import React from 'react';
import { storiesOf } from '@kadira/storybook';
import { Tabs } from 'DeskPRO/Component/Semantic/Tabs';
import { css } from '../../decorators';

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
