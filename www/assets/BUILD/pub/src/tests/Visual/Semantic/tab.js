import React from 'react';
import { storiesOf } from '@storybook/react';
import { Tab, TabGroup } from 'DeskPRO/Component/Semantic/Tab';
import { css } from 'Visual/decorators';

storiesOf('Semantic: tab', module)
  .addDecorator(story => css(story()))
  .add(
    'Tabs',
    () => <TabGroup>
      <Tab key="1" label="First">First tab content</Tab>
      <Tab key="2" label="Second">Second tab content</Tab>
    </TabGroup>
  )
  .add(
    'Tabs with icons',
    () => <TabGroup>
      <Tab key="1" label="First" icon="globe">First tab content</Tab>
      <Tab key="2" label="Second" icon="attach">Second tab content</Tab>
    </TabGroup>
  )
;
