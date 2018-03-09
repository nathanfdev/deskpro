import React from 'react';
import {
  Drawer,
  Heading,
  ItemList,
  Item,
  Count,
} from '@deskpro/react-components';
import PropTypes from 'prop-types';

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
          {filterSet.filters.map((key) => {
            const filter = filters.find(item => item.get('id') === key);
            return (
              <Item
                key={filter.get('id')}
              >
                {filter.get('title')}
                <Count>0</Count>
              </Item>
            );
          })}
        </ItemList>
      </Drawer>
    );
  }
}
