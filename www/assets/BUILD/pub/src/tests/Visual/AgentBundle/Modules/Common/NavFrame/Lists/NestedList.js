import React from 'react';
import { storiesOf, action } from '@storybook/react';
import { SectionsPane, Section, NestedList }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame';
import { css } from '../../../../../decorators';

const flatItems = [{ id: 1, title: 'Music' }, { id: 2, title: 'Videos' }, { id: 3, title: 'Pictures' }];
const itemsNoTitles = [{ id: 1 }, { id: 2, title: 'This one has title' }, { id: 3 }];
const nestedItems = [
  {
    id:     1,
    title:  'Music',
    count:  10,
    nested: [
      { id: 11, title: 'Classical', count: 3 },
      { id: 12, title: 'Electronic', count: 2 },
      { id: 13, title: 'Jazz & Blues', count: 5 }
    ]
  },
  {
    id:     2,
    title:  'Videos',
    count:  15,
    nested: [
      { id: 22, title: 'Music Videos', count: 3 },
      {
        id:     21,
        title:  'Movies',
        count:  9,
        nested: [
          { id: 211, title: 'Comedy', count: 5 },
          { id: 212, title: 'Western', count: 1 },
          { id: 213, title: 'Horror', count: 1 },
          { id: 214, title: 'Science Fiction', count: 2 }
        ]
      },
      { id: 23, title: 'Lectures', count: 3 },
      { id: 23, title: 'Misc', count: 0 }
    ]
  },
  { id: 3, title: 'Pictures', count: 10787 }
];

storiesOf('Common-Nav: NestedList', module)
  .addDecorator(story => css(
    <div style={{ width: '250px' }}>
      <SectionsPane>
        <Section>
          {story()}
        </Section>
      </SectionsPane>
    </div>
  ))
  .add(
    'w/ nested items + onClick',
    () => <NestedList items={nestedItems} onClick={action('onClick')} />
  )
  .add(
    'always expanded + onClick',
    () => <NestedList items={nestedItems} alwaysExpanded onClick={action('onClick')} />
  )
  .add(
    'items w/o titles and counts',
    () => <NestedList items={itemsNoTitles} />
  )
  .add(
    'items w/ titles w/o counts',
    () => <NestedList items={flatItems} />
  )
  .add(
    'onItemControlClick',
    () => <NestedList items={nestedItems} onItemControlClick={() => action('onItemControlClick')} />
  )
  .add(
    'empty state (nothing rendered)',
    () => <NestedList items={undefined} />
  )
;
