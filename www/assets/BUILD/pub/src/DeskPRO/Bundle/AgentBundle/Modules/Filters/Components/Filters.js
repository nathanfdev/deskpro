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

export default class AgentFilters extends React.Component {
  static propTypes = {
    filterSets: PropTypes.array,
    filters:    PropTypes.object,
    stars:      PropTypes.array,
  };

  static defaultProps = {
    filterSets: [],
    filters:    [],
    stars:      [],
  };

  constructor(props) {
    super(props);
    let currentDrawer = '';
    if (props.filterSets.length) {
      currentDrawer = `filterSet${props.filterSets[0].id}`;
    }
    this.state = {
      currentDrawer
    };
    this.drawers = {};
  }

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
    } = this.props;

    return (
      <Column style={{ width: '220px' }}>
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
                filterSet={filterSet}
                filters={filters}
              />
            );
          }
          )}
          <Stars
            ref={(c) => { this.drawers.stars = c; }}
            stars={stars}
            opened={false}
            onChange={this.setDrawer}
          />
        </DrawerList>
      </Column>
    );
  }
}
