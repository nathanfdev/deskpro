import React from 'react';
import { storiesOf, action } from '@storybook/react';
import { SectionsPane, Section, SectionHeader, NestedList }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame';
import { css } from '../../../../decorators';

storiesOf('Common-Nav: Section Header', module)
  .addDecorator(story => css(
    <div style={{ width: '250px' }}>
      <SectionsPane>
        <Section>
          {story()}
          <NestedList items={[{ id: 1, title: 'Demo list beneath the header' }]} />
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
    'w/ label',
    () =>
      <SectionHeader label="Using the label prop" />
  )
  .add(
    'w/ SLA',
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
    'w/ grouping',
    () =>
      <SectionHeader callback={action('action')}>
        Grouped Header
      </SectionHeader>
  )
  .add(
    'w/ count',
    () =>
      <SectionHeader count="999">
        With count
      </SectionHeader>
  )
  .add(
    'w/ grouping and count',
    () =>
      <SectionHeader callback={action('action')} count="999">
        With grouping and count
      </SectionHeader>
  )
;
