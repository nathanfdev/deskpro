import React from 'react';
import PropTypes from 'prop-types';
import {
  Column,
  Heading,
  Icon,
  DrawerList,
  Scrollbar,
} from '@deskpro/react-components';
import { faEnvelope } from '@fortawesome/free-regular-svg-icons';
import { FormattedMessage } from 'react-intl';
import FiltersSet from './FiltersSet';
import Stars from './Stars';
import Labels from './Labels';

export default class AgentFilters extends React.Component {
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
    onSelectMode:       PropTypes.func,
    onGroupingChange:   PropTypes.func,
  };

  static defaultProps = {
    agents:             {},
    filterSets:         {},
    filters:            {},
    groupFields:        {},
    labels:             {},
    stars:              {},
    starsCounts:        {},
    ticketCustomFields: {},
    onSelectMode() {},
    onGroupingChange() {},
  };

  constructor(props) {
    super(props);
    let mode = null;
    if (props.filterSets.size) {
      mode = {
        type:   'filter',
        filter: props.filterSets.first().get('filters').first(),
      };
    }
    let maxHeight;
    const parent = document.getElementById('react_dp_agent_filters');
    if (parent) {
      maxHeight = parent.offsetHeight - 47;
    } else {
      maxHeight = 600;
    }
    window.addEventListener('resize', this.windowResize);
    this.drawers = {};
    this.state = {
      mode,
      maxHeight
    };
  }

  componentWillUnmount = () => {
    window.removeEventListener('resize', this.windowResize);
  };

  onSelectMode = (mode) => {
    this.setState({ mode });
    this.props.onSelectMode(mode);
  };

  windowResize = () => {
    const parent = document.getElementById('react_dp_agent_filters');
    if (!this.resizing) {
      window.requestAnimationFrame(() => {
        this.setState({ maxHeight: parent.offsetHeight - 47 });
        this.resizing = false;
      });
    }
    this.resizing = true;
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
      onGroupingChange,
    } = this.props;
    const {
      mode
    } = this.state;

    return (
      <Column style={{ width: '213px' }} className="agent-filters">
        <Heading>
          <Icon name={faEnvelope} />
          <FormattedMessage id="agent.search.type_ticket" />
        </Heading>
        <DrawerList>
          <Scrollbar
            autoHeightMax={this.state.maxHeight}
            hideTracksWhenNotNeeded
          >
            {filterSets.toArray().map(filterSet => (
              <FiltersSet
                key={filterSet.get('id')}
                ref={(c) => { this.drawers[`filterSet${filterSet.get('id')}`] = c; }}
                opened
                onSelectMode={this.onSelectMode}
                onGroupingChange={onGroupingChange}
                agents={agents}
                agentTeams={agentTeams}
                filterSet={filterSet}
                filters={filters}
                filtersCounts={filtersCounts}
                groupFields={groupFields}
                mode={mode}
                ticketCustomFields={ticketCustomFields}
                ticketDepartments={ticketDepartments}
              />
              )
            )}
            <div className="agent-filters--stars">
              <Stars
                ref={(c) => { this.drawers.stars = c; }}
                stars={stars}
                starsCounts={starsCounts}
                opened
                onSelectMode={this.onSelectMode}
                mode={mode}
              />
            </div>
            <div className="agent-filters--labels">
              <Labels
                ref={(c) => { this.drawers.labels = c; }}
                labels={labels}
                opened
                onSelectMode={this.onSelectMode}
                mode={mode}
              />
            </div>
          </Scrollbar>
        </DrawerList>
      </Column>
    );
  }
}
