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
    filters:   PropTypes.array,
  };

  render() {
    const { filterSet, filters } = this.props;
    return (
      <Drawer>
        <Heading>
          {filterSet.title}
        </Heading>
        <ItemList>
          {filterSet.filters.map((key) => {
            const filter = filters.find(item => item.get('id') === key);
            return (
              <Item>
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
