import React from 'react';
import PropTypes from 'prop-types';
import { connect } from 'react-redux';
import { allSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { SeparateComponent } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/SeparateComponent';
import AgentFilters from './AgentFilters';

@connect(state => ({
  filterSets:    allSelectorFactory('TicketFilterSets')(state),
  filters:       allSelectorFactory('TicketFilters')(state),
  filtersCounts: allSelectorFactory('TicketFilterCounts')(state)
}))
export class AgentFiltersContainer extends SeparateComponent {
  static propTypes = {
    filterSets:    PropTypes.object,
    filters:       PropTypes.object,
    filtersCounts: PropTypes.object,
  };

  static getType() {
    return 'AgentFilters';
  }

  render() {
    const { filterSets, filters, filtersCounts } = this.props;
    console.log(filtersCounts);
    return (
      <AgentFilters
        filterSets={filterSets}
        filters={filters}
        filtersCounts={filtersCounts}
        stars={[]}
        labels={[]}
      />
    );
  }
}
export default AgentFiltersContainer;
