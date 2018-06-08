import React from 'react';
import PropTypes from 'prop-types';
import { connect } from 'react-redux';
import { filterGroupFieldsSettingsSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/Selectors/info';
import { allSelectorFactory, collectionSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { SeparateComponent } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/SeparateComponent';
import { agentsSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/agents';
import AgentFilters from './AgentFilters';
import { loadGrouping } from '../Actions/filterActions';

@connect(state => ({
  agents:             agentsSelector(state),
  agentTeams:         allSelectorFactory('AgentTeam')(state),
  filterSets:         allSelectorFactory('TicketFilterSets')(state),
  filters:            allSelectorFactory('TicketFilters')(state),
  filtersCounts:      allSelectorFactory('TicketFilterCounts')(state),
  groupFields:        filterGroupFieldsSettingsSelector(state),
  labels:             allSelectorFactory('TicketLabels')(state),
  stars:              allSelectorFactory('TicketStars')(state),
  starsCounts:        allSelectorFactory('TicketStarsCounts')(state),
  ticketCustomFields: allSelectorFactory('TicketCustomFields')(state),
  ticketDepartments:  collectionSelectorFactory('Department', 'all_tickets')(state),
}))
class AgentFiltersContainer extends SeparateComponent {
  static propTypes = {
    agents:             PropTypes.object,
    agentTeams:         PropTypes.object,
    filterSets:         PropTypes.object,
    filters:            PropTypes.object,
    filtersCounts:      PropTypes.object,
    groupFields:        PropTypes.object,
    labels:             PropTypes.object,
    stars:              PropTypes.object,
    starsCounts:        PropTypes.object,
    ticketCustomFields: PropTypes.object,
    ticketDepartments:  PropTypes.object,
    dispatch:           PropTypes.func,
  };

  static getType() {
    return 'AgentFilters';
  }

  static resizeList() {
    window.DeskPRO.Agent.ScrollerHandler.updateListPane();
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
    this.loadListPane(mode);
    this.state = {
      mode,
    };
  }


  componentDidMount() {
    window.addEventListener('resize', AgentFiltersContainer.resizeList);
  }

  /* eslint-disable class-methods-use-this */
  componentWillUnmount() {
    window.removeEventListener('resize', AgentFiltersContainer.resizeList);
  }

  onGroupingChange = (id, groupBy) => {
    this.props.dispatch(loadGrouping(id, groupBy));
  };

  loadListPane = (mode) => {
    /* eslint-disable no-undef, camelcase */
    if (DeskPRO_Window) {
      const listPath =
              mode.grouping ?
                `ticket-search/filter/${mode.filter}?subFilterBy=${mode.grouping}&subFilterByValue=${mode.groupingValue}`
                : `ticket-search/filter/${mode.filter}`;
      DeskPRO_Window.loadListPane(listPath, { isBackgroundLoad: false });
    }
    /* eslint-enable no-undef, camelcase */
  };

  onSelectMode = (mode) => {
    if (mode !== this.state.mode) {
      switch (mode.type) {
        case 'filter':
          this.loadListPane(mode);
          break;
        default:
      }
    }
    this.setState({ mode });
  };

  render() {
    const {
      agents,
      agentTeams,
      filterSets,
      filters,
      filtersCounts,
      groupFields,
      labels,
      stars,
      starsCounts,
      ticketCustomFields,
      ticketDepartments,
    } = this.props;
    return (
      <AgentFilters
        agents={agents}
        agentTeams={agentTeams}
        filterSets={filterSets}
        filters={filters}
        filtersCounts={filtersCounts}
        groupFields={groupFields}
        labels={labels}
        stars={stars}
        starsCounts={starsCounts}
        ticketCustomFields={ticketCustomFields}
        ticketDepartments={ticketDepartments}
        onSelectMode={this.onSelectMode}
        onGroupingChange={this.onGroupingChange}
      />
    );
  }
}
export default AgentFiltersContainer;
