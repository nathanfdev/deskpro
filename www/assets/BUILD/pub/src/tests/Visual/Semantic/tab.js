import React from 'react';
import { storiesOf } from '@kadira/storybook';
import { Tab, TabGroup } from 'DeskPRO/Component/Semantic/Tab';

storiesOf('Semantic: tab', module)
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
