import React from 'react';
import { storiesOf, action } from '@kadira/storybook';
import { decorate } from 'stories/decorate';
import { SectionsPane, Section, SectionHeader, SectionGroupedHeader, NestedList }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame';

storiesOf('Common: Section Header', module)
  .addDecorator(story => decorate(
    <div style={{width: '250px'}}>
      <SectionsPane>
        <Section>
          {story()}
          <NestedList items={[{id: 1, title: 'Demo list beneath the header'}]} />
        </Section>
      </SectionsPane>
    </div>
  ))
  .add(
    'Simple',
    () =>
      <SectionHeader>Simple Header</SectionHeader>
  )
  .add(
    'Simple w/ SLA',
    () =>
      <SectionHeader>
        Demo SLA
        <div className="sla" style={{ display: 'inline-block', float: 'right' }}>
          <span className="sla-button selected" onClick={action('onClick')}>Mine</span>
          <span className="sla-button" onClick={action('onClick')}>All</span>
        </div>
      </SectionHeader>
  )
  .add(
    'Grouped w/o label, count, callback',
    () =>
      <SectionGroupedHeader>Grouped Header</SectionGroupedHeader>
  )
  .add(
    'Grouped w/ label, count, callback',
    () =>
      <SectionGroupedHeader
        label="Grouped Header"
        count="999"
        callback={action('action')}>
      </SectionGroupedHeader>
  )
;
