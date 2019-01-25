import React from 'react';
import { storiesOf } from '@storybook/react';
import { action } from '@storybook/addon-actions';
import { css, redux } from 'Visual/decorators';
import AgentFilters from 'DeskPRO/Bundle/AgentBundle/Modules/Filters/Components/AgentFilters';
import { filterSets, filters, filtersCounts, stars, starsCounts, labels } from 'DemoState/AgentBundle/Modules/Filters/filters';

storiesOf('Agent: Filters', module)
  .addDecorator(story => css(story()))
  .addDecorator(story => redux({}, story()))
  .add(
    'Filters',
    () =>
      <div id="react_dp_agent_filters">
        <AgentFilters
          filterSets={filterSets}
          filters={filters}
          filtersCounts={filtersCounts}
          stars={stars}
          starsCounts={starsCounts}
          labels={labels}
          onSelectMode={action('Select Mode')}
        />
      </div>
  )
;
