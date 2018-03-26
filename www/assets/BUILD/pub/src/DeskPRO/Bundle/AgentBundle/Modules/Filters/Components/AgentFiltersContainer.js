import React from 'react';
import { SeparateComponent } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/SeparateComponent';
import { filterSets, filters, stars, labels } from 'DemoState/AgentBundle/Modules/Filters/filters';
import AgentFilters from './AgentFilters';

export class AgentFiltersContainer extends SeparateComponent {
  static getType() {
    return 'AgentFilters';
  }

  render() {
    return (
      <AgentFilters
        filterSets={filterSets}
        filters={filters}
        stars={stars}
        labels={labels}
      />
    );
  }
}
export default AgentFiltersContainer;
