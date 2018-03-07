import React from 'react';
import PropTypes from 'prop-types';
import {
  Column,
  Heading,
  Icon,
  DrawerList
} from '@deskpro/react-components';
import FiltersSet from './FiltersSet';

export default class AgentFilters extends React.Component {
  static propTypes = {
    filterSets: PropTypes.array,
    filters:    PropTypes.array,
  };

  static defaultProps = {
    filterSets: [],
    filters:    [],
  };

  render() {
    const {
      filterSets,
      filters,
    } = this.props;

    return (
      <Column style={{ width: '220px' }}>
        <Heading>
          <Icon name="envelope-o" />
          Tickets
        </Heading>
        <DrawerList>
          {filterSets.map(filterSet =>
            <FiltersSet
              filterSet={filterSet}
              filters={filters}
            />
          )}
        </DrawerList>
      </Column>
    );
  }
}
