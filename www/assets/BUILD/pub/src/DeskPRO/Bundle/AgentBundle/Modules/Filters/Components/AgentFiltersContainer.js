import React from 'react';
import PropTypes from 'prop-types';
import { connect } from 'react-redux';
import { filterGroupFieldsSettingsSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/Selectors/info';
import { allSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { SeparateComponent } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/SeparateComponent';
import AgentFilters from './AgentFilters';

@connect(state => ({
  filterSets:         allSelectorFactory('TicketFilterSets')(state),
  filters:            allSelectorFactory('TicketFilters')(state),
  filtersCounts:      allSelectorFactory('TicketFilterCounts')(state),
  groupFields:        filterGroupFieldsSettingsSelector(state),
  labels:             allSelectorFactory('TicketLabels')(state),
  stars:              allSelectorFactory('TicketStars')(state),
  starsCounts:        allSelectorFactory('TicketStarsCounts')(state),
  ticketCustomFields: allSelectorFactory('TicketCustomFields')(state),
}))
export class AgentFiltersContainer extends SeparateComponent {
  static propTypes = {
    filterSets:         PropTypes.object,
    filters:            PropTypes.object,
    filtersCounts:      PropTypes.object,
    groupFields:        PropTypes.object,
    labels:             PropTypes.object,
    stars:              PropTypes.object,
    starsCounts:        PropTypes.object,
    ticketCustomFields: PropTypes.object,
  };

  static getType() {
    return 'AgentFilters';
  }

  render() {
    const {
      filterSets,
      filters,
      filtersCounts,
      groupFields,
      labels,
      stars,
      starsCounts,
      ticketCustomFields,
    } = this.props;
    return (
      <AgentFilters
        filterSets={filterSets}
        filters={filters}
        filtersCounts={filtersCounts}
        groupFields={groupFields}
        labels={labels}
        stars={stars}
        starsCounts={starsCounts}
        ticketCustomFields={ticketCustomFields}
      />
    );
  }
}
export default AgentFiltersContainer;
