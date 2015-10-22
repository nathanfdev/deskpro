import React, {PropTypes} from 'react';
import Menu from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Menu';
import Item from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Item';
import {FilterItem} from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/FilterItem';

const FilterByDropdown = React.createClass({

  propTypes: {
    filterOptions: PropTypes.array.isRequired,
    currentFilterMode: PropTypes.object.isRequired,
    toggleDropdown: PropTypes.func.isRequired
  },

  mixins: [require('react-onclickoutside')],

  getInitialState() {
    return {
      category: { value: false },
      status: { value: { some: true } },
      custom_category: { value: false }
    };
  },

  handleClickOutside() {
    this.props.toggleDropdown();
  },

  resetFilter(type) {
    this.setState({
      [type]: { value: false }
    });
  },

  render() {
    return (
      <Menu>
        <FilterItem
          filterType="category"
          isActive={Boolean(this.state.category.value)}
          resetFilter={this.resetFilter}
          icon="calendar-o"
          label="Type"
          >
          <Menu>
            <Item label="Sub-menu 2"/>
            <Item label="Subterranean"/>
          </Menu>
        </FilterItem>
        <FilterItem
          filterType="status"
          isActive={Boolean(this.state.status.value)}
          resetFilter={this.resetFilter}
          icon="calendar-o"
          label="Status">
          <Menu>
            <Item label="Sub-menu 2"/>
            <Item label="Subterranean"/>
          </Menu>
        </FilterItem>
        <FilterItem
          filterType="custom_category"
          isActive={Boolean(this.state.custom_category.value)}
          resetFilter={this.resetFilter}
          icon="calendar-o"
          label="Category">
          <Menu>
            <Item label="Sub-menu 2"/>
            <Item label="Subterranean"/>
          </Menu>
        </FilterItem>
      </Menu>
    );
  }
});

module.exports.FilterByDropdown = FilterByDropdown;
