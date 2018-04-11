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
    filterSet:          PropTypes.object,
    filters:            PropTypes.object,
    filtersCounts:      PropTypes.object,
    groupFields:        PropTypes.object,
    ticketCustomFields: PropTypes.object,
    onChange:           PropTypes.func,
    onSelectMode:       PropTypes.func,
    onGroupingChange:   PropTypes.func,
    opened:             PropTypes.bool,
    mode:               PropTypes.object,
  };

  onSelectFilter = (key) => {
    this.props.onSelectMode({ type: 'filter', filter: key });
  };

  close() {
    this.drawer.close();
  }

  render() {
    const {
      filterSet,
      filters,
      filtersCounts,
      groupFields,
      ticketCustomFields,
      onChange,
      onGroupingChange,
      onSelectMode,
      opened,
      mode,
    } = this.props;
    const items = [];
    filterSet.get('filters').forEach((key) => {
      const filter = filters.find(item => item.get('id') === key);
      if (filter) {
        items.push(
          <Filter
            key={filter.get('id')}
            filter={filter}
            filtersCounts={filtersCounts}
            groupFields={groupFields}
            ticketCustomFields={ticketCustomFields}
            mode={mode}
            onSelect={() => this.onSelectFilter(key)}
            onSelectMode={onSelectMode}
            onGroupingChange={onGroupingChange}
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
