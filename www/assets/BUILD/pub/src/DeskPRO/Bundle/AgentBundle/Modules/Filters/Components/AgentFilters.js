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
    filterSets:    PropTypes.object,
    filters:       PropTypes.object,
    filtersCounts: PropTypes.object,
    stars:         PropTypes.object,
    starsCounts:   PropTypes.object,
    labels:        PropTypes.array,
    onSelectMode:  PropTypes.func,
  };

  static defaultProps = {
    filterSets:  {},
    filters:     {},
    stars:       {},
    starsCounts: {},
    labels:      [],
    onSelectMode() {},
  };

  constructor(props) {
    super(props);
    let currentDrawer = '';
    let mode = null;
    if (props.filterSets.size) {
      currentDrawer = `filterSet${props.filterSets.first().get('id')}`;
      mode = {
        type:   'filter',
        filter: props.filterSets.first().get('filters').first(),
      };
    }
    this.state = {
      currentDrawer,
      mode,
    };
    this.drawers = {};
  }

  onSelectMode = (mode) => {
    this.setState({ mode });
    this.props.onSelectMode(mode);
  };

  setDrawer = (drawer) => {
    if (drawer.isOpen()) {
      this.setState({
        currentDrawer: drawer.id
      });
      Object.entries(this.drawers).forEach(([key, object]) => {
        if (key !== drawer.id && object) {
          object.close();
        }
      });
    }
  };

  render() {
    const {
      filterSets,
      filters,
      filtersCounts,
      stars,
      starsCounts,
      labels,
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
          {filterSets.toArray().map((filterSet) => {
            const opened = `filterSet${filterSet.get('id')}` === this.state.currentDrawer;
            return (
              <FiltersSet
                key={filterSet.get('id')}
                ref={(c) => { this.drawers[`filterSet${filterSet.get('id')}`] = c; }}
                opened={opened}
                onChange={this.setDrawer}
                onSelectMode={this.onSelectMode}
                filterSet={filterSet}
                filters={filters}
                filtersCounts={filtersCounts}
                mode={mode}
              />
            );
          }
          )}
          <Stars
            ref={(c) => { this.drawers.stars = c; }}
            stars={stars}
            starsCounts={starsCounts}
            opened={false}
            onChange={this.setDrawer}
            onSelectMode={this.onSelectMode}
            mode={mode}
          />
          <Labels
            ref={(c) => { this.drawers.labels = c; }}
            labels={labels}
            opened={false}
            onChange={this.setDrawer}
            onSelectMode={this.onSelectMode}
            mode={mode}
          />
        </DrawerList>
      </Column>
    );
  }
}
