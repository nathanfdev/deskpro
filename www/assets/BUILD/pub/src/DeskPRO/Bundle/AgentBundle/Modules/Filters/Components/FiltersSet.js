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
    filterSet:    PropTypes.object,
    filters:      PropTypes.object,
    onChange:     PropTypes.func,
    onSelectMode: PropTypes.func,
    opened:       PropTypes.bool,
    mode:         PropTypes.object,
  };

  onSelectFilter = (key) => {
    this.props.onSelectMode({ type: 'filter', filter: key });
    /* eslint-disable no-undef, camelcase */
    if (DeskPRO_Window) {
      DeskPRO_Window.loadListPane(`ticket-search/filter/${key}`, { isBackgroundLoad: false });
    }
    /* eslint-enable no-undef, camelcase */
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
      mode,
    } = this.props;
    const items = [];
    filterSet.get('filters').forEach((key) => {
      const filter = filters.find(item => item.get('id') === key);
      const selected = mode && mode.filter === key;
      if (filter) {
        items.push(
          <Filter
            key={filter.get('id')}
            filter={filter}
            selected={selected}
            onSelect={() => this.onSelectFilter(key)}
          />
        );
      }
    });
    return (
      <Drawer
        onChange={onChange}
        opened={opened}
        id={`filterSet${filterSet.get('id')}`}
        ref={(c) => { this.drawer = c; }}
      >
        <Heading>
          {filterSet.get('title')}
        </Heading>
        <ItemList on="mouseOver">
          {items}
        </ItemList>
      </Drawer>
    );
  }
}
