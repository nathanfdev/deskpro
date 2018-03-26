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
    filterSets:   PropTypes.array,
    filters:      PropTypes.object,
    stars:        PropTypes.array,
    labels:       PropTypes.array,
    onSelectMode: PropTypes.func,
  };

  static defaultProps = {
    filterSets: [],
    filters:    [],
    stars:      [],
    labels:     [],
    onSelectMode() {},
  };

  constructor(props) {
    super(props);
    let currentDrawer = '';
    let mode = null;
    if (props.filterSets.length) {
      currentDrawer = `filterSet${props.filterSets[0].id}`;
      mode = {
        type:   'filter',
        filter: props.filterSets[0].filters[0],
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
      stars,
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
          {filterSets.map((filterSet) => {
            const opened = `filterSet${filterSet.id}` === this.state.currentDrawer;
            return (
              <FiltersSet
                key={filterSet.id}
                ref={(c) => { this.drawers[`filterSet${filterSet.id}`] = c; }}
                opened={opened}
                onChange={this.setDrawer}
                onSelectMode={this.onSelectMode}
                filterSet={filterSet}
                filters={filters}
                mode={mode}
              />
            );
          }
          )}
          <Stars
            ref={(c) => { this.drawers.stars = c; }}
            stars={stars}
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
