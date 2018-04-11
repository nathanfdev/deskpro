import React from 'react';
import PropTypes from 'prop-types';
import { connect } from 'react-redux';
import { filterGroupFieldsSettingsSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/Selectors/info';
import { allSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { SeparateComponent } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/SeparateComponent';
import AgentFilters from './AgentFilters';
import { loadGrouping } from '../Actions/filterActions';

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
    dispatch:           PropTypes.func,
  };

  static getType() {
    return 'AgentFilters';
  }

  constructor(props) {
    super(props);
    let mode = null;
    if (props.filterSets.size) {
      mode = {
        type:   'filter',
        filter: props.filterSets.first().get('filters').first(),
      };
    }
    this.state = {
      mode,
    };
  }

  onGroupingChange = (id, groupBy) => {
    this.props.dispatch(loadGrouping(id, groupBy));
  };

  onSelectMode = (mode) => {
    console.log(mode);
    if (mode !== this.state.mode) {
      switch (mode.type) {
        case 'filter':
          /* eslint-disable no-undef, camelcase */
          if (DeskPRO_Window) {
            const listPath =
              mode.grouping ?
                `ticket-search/filter/${mode.filter}?subFilterBy=${mode.grouping}&subFilterByValue=${mode.groupingValue}`
                : `ticket-search/filter/${mode.filter}`;
            DeskPRO_Window.loadListPane(listPath, { isBackgroundLoad: false });
          }
          /* eslint-enable no-undef, camelcase */
          break;
        default:
      }
    }
    this.setState({ mode });
  };

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
        onSelectMode={this.onSelectMode}
        onGroupingChange={this.onGroupingChange}
      />
    );
  }
}
export default AgentFiltersContainer;
