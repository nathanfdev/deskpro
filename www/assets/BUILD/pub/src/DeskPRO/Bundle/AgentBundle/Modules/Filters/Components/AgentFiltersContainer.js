import React from 'react';
import PropTypes from 'prop-types';
import { connect } from 'react-redux';
import { allSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { SeparateComponent } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/SeparateComponent';
import AgentFilters from './AgentFilters';

@connect(state => ({
  filterSets:    allSelectorFactory('TicketFilterSets')(state),
  filters:       allSelectorFactory('TicketFilters')(state),
  filtersCounts: allSelectorFactory('TicketFilterCounts')(state),
  labels:        allSelectorFactory('TicketLabels')(state),
  stars:         allSelectorFactory('TicketStars')(state),
  starsCounts:   allSelectorFactory('TicketStarsCounts')(state),
}))
export class AgentFiltersContainer extends SeparateComponent {
  static propTypes = {
    filterSets:    PropTypes.object,
    filters:       PropTypes.object,
    filtersCounts: PropTypes.object,
    labels:        PropTypes.object,
    stars:         PropTypes.object,
    starsCounts:   PropTypes.object,
  };

  static getType() {
    return 'AgentFilters';
  }

  render() {
    const {
      filterSets,
      filters,
      filtersCounts,
      labels,
      stars,
      starsCounts,
    } = this.props;
    return (
      <AgentFilters
        filterSets={filterSets}
        filters={filters}
        filtersCounts={filtersCounts}
        labels={labels}
        stars={stars}
        starsCounts={starsCounts}
      />
    );
  }
}
export default AgentFiltersContainer;
