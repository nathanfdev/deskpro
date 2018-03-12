import React from 'react';
import PropTypes from 'prop-types';
import {
  Drawer,
  Heading,
  ItemList,
} from '@deskpro/react-components';
import Filter from './Filter';

export default class FiltersSet extends React.Component {
  static propTypes = {
    filterSet: PropTypes.object,
    filters:   PropTypes.object,
    onChange:  PropTypes.func,
    opened:    PropTypes.bool,
  };

  close() {
    this.drawer.close();
  }

  render() {
    const {
      filterSet,
      filters,
      onChange,
      opened,
    } = this.props;
    const items = [];
    filterSet.filters.forEach((key) => {
      const filter = filters.find(item => item.get('id') === key);
      if (filter) {
        items.push(
          <Filter
            key={filter.get('id')}
            filter={filter}
          />
        );
      }
    });
    return (
      <Drawer
        onChange={onChange}
        opened={opened}
        id={`filterSet${filterSet.id}`}
        ref={(c) => { this.drawer = c; }}
      >
        <Heading>
          {filterSet.title}
        </Heading>
        <ItemList>
          {items}
        </ItemList>
      </Drawer>
    );
  }
}
