import React from 'react';
import { storiesOf, action } from '@kadira/storybook';
import { decorate } from 'tests/visual/decorate';

storiesOf('Nav: Tickets', module)
  .addDecorator(story => decorate(
    <div style={{width: '250px'}}>
      <SectionsPane>
        <Section>
          {story()}
        </Section>
      </SectionsPane>
    </div>
  ))
  .add(
    'w/ nested items + onClick',
    () =>
      <NestedList items={nestedItems} onClick={action('onClick')} />
  )
;
