import React from 'react';
import { storiesOf } from '@storybook/react';
import { SegmentsGroup, Segment } from 'DeskPRO/Component/Semantic/Segment';
import { List } from 'DeskPRO/Component/Semantic/List';
import { structure as listStructure } from '../list';
import { css } from '../../decorators';

const segmentWithHeader = {
  header: {
    size:    1,
    content: 'H1 Header!'
  }
};

const segmentWithDefaultHeader = {
  header: {
    content: 'Default header'
  }
};

const segmentWithList = {
  header: {
    content: 'Segment with list included'
  }
};


storiesOf('Semantic: segments', module)
  .addDecorator(story => css(story()))
  .add(
    'Segments group',
    () =>
      <div>
        <SegmentsGroup>
          <Segment>
            Group segment 1
          </Segment>
          <Segment classes={['red']}>
            Group segment 2 (red)
          </Segment>
          <Segment classes={['brown']}>
            Group segment 3 (brown)
          </Segment>
        </SegmentsGroup>
        <SegmentsGroup raised>
          <Segment>
            Two raised
          </Segment>
          <Segment classes={['blue']}>
            segments
          </Segment>
        </SegmentsGroup>
        <SegmentsGroup stacked>
          <Segment {...segmentWithDefaultHeader}>
            <p>Two stacked</p>
          </Segment>
          <Segment classes={['orange']}>
            segments with header (don&apos;t forget small headers are inline)
          </Segment>
        </SegmentsGroup>
        <SegmentsGroup raised>
          <Segment>
            Two piled
          </Segment>
          <Segment classes={['orange']}>
            segments
          </Segment>
        </SegmentsGroup>
        <SegmentsGroup horizontal piled>
          <Segment>
            horizontal piled
          </Segment>
          <Segment classes={['black']}>
            segments, one is black
          </Segment>
        </SegmentsGroup>
      </div>
  )
  .add(
    'Segment',
    () =>
      <div>
        <Segment {...segmentWithHeader}>
          With header
        </Segment>
        <Segment {...segmentWithList}>
          <List {...listStructure} />
        </Segment>
        <Segment raised>
          Raised segment
        </Segment>
        <Segment piled classes={['red']}>
          Piled
        </Segment>
        <Segment stacked loading classes={['blue']}>
          Stacked loading segment
        </Segment>
        <Segment raised disiabled classes={['orange']}>
          Raised disabled segment
        </Segment>
      </div>
  )
;
