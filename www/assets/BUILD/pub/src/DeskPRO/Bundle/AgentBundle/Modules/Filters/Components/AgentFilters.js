import React from 'react';
import PropTypes from 'prop-types';
import {
  Column,
  Heading,
  Icon,
  DrawerList,
} from '@deskpro/react-components';
import FiltersSet from './FiltersSet';
import Stars from './Stars';
import Labels from './Labels';

export default class AgentFilters extends React.Component {
  static propTypes = {
    filterSets:         PropTypes.object,
    filters:            PropTypes.object,
    filtersCounts:      PropTypes.object,
    groupFields:        PropTypes.object,
    labels:             PropTypes.object,
    stars:              PropTypes.object,
    starsCounts:        PropTypes.object,
    ticketCustomFields: PropTypes.object,
    onSelectMode:       PropTypes.func,
  };

  static defaultProps = {
    filterSets:         {},
    filters:            {},
    groupFields:        {},
    labels:             {},
    stars:              {},
    starsCounts:        {},
    ticketCustomFields: {},
    onSelectMode() {},
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
    this.state = {
      mode,
    };
    this.drawers = {};
  }

  onSelectMode = (mode) => {
    this.setState({ mode });
    this.props.onSelectMode(mode);
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
    const {
      mode
    } = this.state;

    return (
      <Column style={{ width: '213px' }} className="agent-filters">
        <Heading>
          <Icon name="envelope-o" />
          Tickets
        </Heading>
        <DrawerList>
          {filterSets.toArray().map(filterSet => (

              <FiltersSet
                key={filterSet.get('id')}
                ref={(c) => { this.drawers[`filterSet${filterSet.get('id')}`] = c; }}
                opened
                onSelectMode={this.onSelectMode}
                filterSet={filterSet}
                filters={filters}
                filtersCounts={filtersCounts}
                groupFields={groupFields}
                mode={mode}
                ticketCustomFields={ticketCustomFields}
              />
            )
          )}
          <Stars
            ref={(c) => { this.drawers.stars = c; }}
            stars={stars}
            starsCounts={starsCounts}
            opened
            onSelectMode={this.onSelectMode}
            mode={mode}
          />
          <Labels
            ref={(c) => { this.drawers.labels = c; }}
            labels={labels}
            opened
            onSelectMode={this.onSelectMode}
            mode={mode}
          />
        </DrawerList>
      </Column>
    );
  }
}
