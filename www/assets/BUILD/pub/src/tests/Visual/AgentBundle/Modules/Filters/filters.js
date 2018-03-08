import React from 'react';
import { storiesOf } from '@storybook/react';
import { css } from 'Visual/decorators';
import AgentFilters from 'DeskPRO/Bundle/AgentBundle/Modules/Filters/Components/Filters';
import { filterSets, filters, stars } from 'DemoState/AgentBundle/Modules/Filters/filters';

storiesOf('Agent: Filters', module)
  .addDecorator(story => css(story()))
  .add(
    'Filters',
    () =>
      <div id="react_dp_agent_filters">
        <AgentFilters
          filterSets={filterSets}
          filters={filters}
          stars={stars}
        />
      </div>
  )
;
